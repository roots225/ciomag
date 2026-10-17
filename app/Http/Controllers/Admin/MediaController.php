<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MediaController extends Controller
{
    public function index(): View
    {
        $mediaItems = Media::with('uploader')
            ->orderByDesc('created_at')
            ->paginate(16);

        return view('admin.media.index', compact('mediaItems'));
    }

    /**
     * API JSON : Liste et recherche des médias pour le composant universel Media Manager.
     */
    public function apiIndex(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('q', ''));

        $items = Media::query()
            ->when($search !== '', function ($q) use ($search) {
                $q->where('file_name', 'like', "%{$search}%")
                    ->orWhere('alt_text', 'like', "%{$search}%")
                    ->orWhere('caption', 'like', "%{$search}%");
            })
            ->orderByDesc('created_at')
            ->limit(60)
            ->get()
            ->map(fn (Media $m) => $this->formatMediaJson($m));

        return response()->json([
            'data' => $items,
        ]);
    }

    /**
     * API JSON : Upload de fichier ou ajout d'URL externe depuis la modale Media Manager.
     */
    public function apiStore(Request $request): JsonResponse
    {
        $media = $this->handleStoreMedia($request);

        return response()->json([
            'message' => 'Média enregistré avec succès.',
            'media' => $this->formatMediaJson($media),
        ], 201);
    }

    /**
     * API JSON : Mise à jour des métadonnées (file_name, alt_text, caption).
     */
    public function apiUpdate(Request $request, Media $media): JsonResponse
    {
        $validated = $request->validate([
            'file_name' => ['required', 'string', 'max:255'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:1000'],
        ]);

        $media->update($validated);

        return response()->json([
            'message' => 'Métadonnées mises à jour.',
            'media' => $this->formatMediaJson($media->fresh()),
        ]);
    }

    /**
     * API JSON : Sauvegarde d'une image recadrée via Cropper.js (nouvelle copie ou remplacement).
     */
    public function apiCrop(Request $request, Media $media): JsonResponse
    {
        $validated = $request->validate([
            'image_base64' => ['required', 'string'],
            'mode' => ['nullable', 'in:new,replace'],
            'width' => ['nullable', 'integer'],
            'height' => ['nullable', 'integer'],
        ]);

        $base64 = $validated['image_base64'];
        if (str_contains($base64, ',')) {
            [, $base64] = explode(',', $base64, 2);
        }

        $binary = base64_decode($base64, true);
        if ($binary === false) {
            return response()->json(['message' => 'Données d\'image recadrée invalides.'], 422);
        }

        $relativePath = 'media/'.date('Y/m').'/crop-'.Str::random(10).'.jpg';
        Storage::disk('public')->put($relativePath, $binary);

        $publicUrl = Storage::disk('public')->url($relativePath);
        $fileSize = strlen($binary);

        $dimensions = @getimagesizefromstring($binary);
        $width = is_array($dimensions) ? ($dimensions[0] ?? ($validated['width'] ?? null)) : ($validated['width'] ?? null);
        $height = is_array($dimensions) ? ($dimensions[1] ?? ($validated['height'] ?? null)) : ($validated['height'] ?? null);

        $mode = $validated['mode'] ?? 'new';

        if ($mode === 'replace') {
            if (! str_starts_with($media->file_path, 'http')) {
                Storage::disk('public')->delete($media->file_path);
            }

            $media->update([
                'file_path' => $relativePath,
                'mime_type' => 'image/jpeg',
                'file_size' => $fileSize,
                'width' => $width,
                'height' => $height,
                'variants' => [
                    'thumbnail' => $publicUrl,
                    'medium' => $publicUrl,
                ],
            ]);

            return response()->json([
                'message' => 'Image recadrée et remplacée avec succès.',
                'media' => $this->formatMediaJson($media->fresh()),
            ]);
        }

        $baseName = pathinfo($media->file_name, PATHINFO_FILENAME);
        $newMedia = Media::create([
            'uploader_id' => $request->user()->id,
            'file_name' => $baseName.'-recadre-'.date('His').'.jpg',
            'file_path' => $relativePath,
            'mime_type' => 'image/jpeg',
            'file_size' => $fileSize,
            'width' => $width,
            'height' => $height,
            'alt_text' => $media->alt_text,
            'caption' => $media->caption,
            'variants' => [
                'thumbnail' => $publicUrl,
                'medium' => $publicUrl,
            ],
        ]);

        return response()->json([
            'message' => 'Nouvelle image recadrée créée dans la médiathèque.',
            'media' => $this->formatMediaJson($newMedia),
        ], 201);
    }

    /**
     * API JSON : Suppression d'un média.
     */
    public function apiDestroy(Media $media): JsonResponse
    {
        if (! str_starts_with($media->file_path, 'http')) {
            Storage::disk('public')->delete($media->file_path);
        }

        $media->delete();

        return response()->json([
            'message' => 'Média supprimé.',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->handleStoreMedia($request);

        return back()->with('success', 'Média enregistré dans la médiathèque.');
    }

    public function update(Request $request, Media $media): RedirectResponse
    {
        $validated = $request->validate([
            'file_name' => ['nullable', 'string', 'max:255'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:1000'],
        ]);

        $media->update(array_filter($validated, fn ($v) => $v !== null));

        return back()->with('success', 'Métadonnées du média mises à jour.');
    }

    public function destroy(Media $media): RedirectResponse
    {
        if (! str_starts_with($media->file_path, 'http')) {
            Storage::disk('public')->delete($media->file_path);
        }

        $media->delete();

        return back()->with('success', 'Média supprimé.');
    }

    private function handleStoreMedia(Request $request): Media
    {
        if ($request->hasFile('file')) {
            $request->validate([
                'file' => ['required', 'file', 'max:20480'],
                'alt_text' => ['nullable', 'string', 'max:255'],
                'caption' => ['nullable', 'string', 'max:1000'],
            ]);

            $uploadedFile = $request->file('file');
            $path = $uploadedFile->store('media/'.date('Y/m'), 'public');

            $width = null;
            $height = null;
            if (str_starts_with((string) $uploadedFile->getMimeType(), 'image/')) {
                $dimensions = @getimagesize($uploadedFile->getRealPath());
                if (is_array($dimensions)) {
                    $width = $dimensions[0] ?? null;
                    $height = $dimensions[1] ?? null;
                }
            }

            $publicUrl = Storage::disk('public')->url($path);

            return Media::create([
                'uploader_id' => $request->user()->id,
                'file_name' => $uploadedFile->getClientOriginalName(),
                'file_path' => $path,
                'mime_type' => $uploadedFile->getMimeType() ?: 'application/octet-stream',
                'file_size' => $uploadedFile->getSize(),
                'width' => $width,
                'height' => $height,
                'alt_text' => $request->input('alt_text'),
                'caption' => $request->input('caption'),
                'variants' => [
                    'thumbnail' => $publicUrl,
                    'medium' => $publicUrl,
                ],
            ]);
        }

        $validated = $request->validate([
            'external_url' => ['required', 'url', 'max:1000'],
            'file_name' => ['required', 'string', 'max:255'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:1000'],
        ]);

        return Media::create([
            'uploader_id' => $request->user()->id,
            'file_name' => $validated['file_name'],
            'file_path' => $validated['external_url'],
            'mime_type' => 'image/jpeg',
            'file_size' => 250000,
            'width' => 1400,
            'height' => 900,
            'alt_text' => $validated['alt_text'] ?? null,
            'caption' => $validated['caption'] ?? null,
            'variants' => [
                'thumbnail' => $validated['external_url'],
                'medium' => $validated['external_url'],
            ],
        ]);
    }

    private function formatMediaJson(Media $media): array
    {
        return [
            'id' => $media->id,
            'file_name' => $media->file_name,
            'url' => $media->url(),
            'thumbnail' => $media->url('thumbnail'),
            'mime_type' => $media->mime_type,
            'file_size' => $media->file_size,
            'human_size' => $media->humanFileSize(),
            'width' => $media->width,
            'height' => $media->height,
            'alt_text' => $media->alt_text ?? '',
            'caption' => $media->caption ?? '',
            'is_image' => $media->isImage(),
            'created_at' => $media->created_at?->format('d/m/Y H:i'),
        ];
    }
}
