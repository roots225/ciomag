<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Podcast;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PodcastAdminController extends Controller
{
    public function index(Request $request): View
    {
        $source = $request->query('source');
        $perPage = min(50, max(1, (int) $request->query('per_page', 10)));

        $podcasts = Podcast::query()
            ->with('category')
            ->when($source, fn ($q) => $q->where('source_type', $source))
            ->orderByDesc('season_number')
            ->orderByDesc('episode_number')
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.podcasts.index', compact('podcasts', 'source'));
    }

    public function create(): View
    {
        $podcast = new Podcast([
            'source_type' => 'youtube',
            'season_number' => 2,
            'episode_number' => (int) Podcast::max('episode_number') + 1,
            'duration_label' => '35:00',
            'host_name' => 'Rédaction CIOMAG',
            'status' => 'published',
            'is_featured' => false,
            'published_at' => now(),
            'social_distribution' => [
                'youtube' => ['enabled' => true, 'status' => 'ready'],
                'facebook' => ['enabled' => true, 'status' => 'ready'],
                'linkedin' => ['enabled' => true, 'status' => 'ready'],
                'twitter' => ['enabled' => true, 'status' => 'ready'],
            ],
        ]);

        $categories = Category::orderBy('display_order')->get();

        return view('admin.podcasts.form', compact('podcast', 'categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePodcast($request);

        $slug = ! empty($validated['slug'])
            ? Str::slug($validated['slug'])
            : Str::slug($validated['title']);

        $videoFilePath = $validated['video_file_path'] ?? null;
        if ($request->hasFile('video_file')) {
            $videoFilePath = $request->file('video_file')->store('podcasts/videos', 'public');
        }

        $youtubeId = Podcast::extractYoutubeId($validated['youtube_url'] ?? null);

        Podcast::create([
            'title' => $validated['title'],
            'slug' => $slug,
            'season_number' => (int) ($validated['season_number'] ?? 1),
            'episode_number' => (int) ($validated['episode_number'] ?? 1),
            'excerpt' => $validated['excerpt'] ?? null,
            'description' => $validated['description'] ?? null,
            'source_type' => $validated['source_type'],
            'youtube_url' => $validated['youtube_url'] ?? null,
            'youtube_video_id' => $youtubeId,
            'video_file_path' => $videoFilePath,
            'thumbnail_url' => $validated['thumbnail_url'] ?? null,
            'duration_label' => $validated['duration_label'] ?? '30:00',
            'guest_name' => $validated['guest_name'] ?? null,
            'guest_role' => $validated['guest_role'] ?? null,
            'host_name' => $validated['host_name'] ?? 'Rédaction CIOMAG',
            'category_id' => $validated['category_id'] ?: null,
            'status' => $validated['status'],
            'is_featured' => $request->boolean('is_featured'),
            'published_at' => $validated['published_at'] ?? now(),
            'social_distribution' => [
                'youtube' => ['enabled' => $request->boolean('dist_youtube', true), 'status' => 'ready'],
                'facebook' => ['enabled' => $request->boolean('dist_facebook', true), 'status' => 'ready'],
                'linkedin' => ['enabled' => $request->boolean('dist_linkedin', true), 'status' => 'ready'],
                'twitter' => ['enabled' => $request->boolean('dist_twitter', true), 'status' => 'ready'],
            ],
        ]);

        return redirect()->route('admin.podcasts.index')
            ->with('success', 'Épisode de podcast enregistré avec succès.');
    }

    public function edit(Podcast $podcast): View
    {
        $categories = Category::orderBy('display_order')->get();

        return view('admin.podcasts.form', compact('podcast', 'categories'));
    }

    public function update(Request $request, Podcast $podcast): RedirectResponse
    {
        $validated = $this->validatePodcast($request, $podcast);

        $slug = ! empty($validated['slug'])
            ? Str::slug($validated['slug'])
            : Str::slug($validated['title']);

        $videoFilePath = $validated['video_file_path'] ?? $podcast->video_file_path;
        if ($request->hasFile('video_file')) {
            $videoFilePath = $request->file('video_file')->store('podcasts/videos', 'public');
        }

        $youtubeId = Podcast::extractYoutubeId($validated['youtube_url'] ?? null);

        $podcast->update([
            'title' => $validated['title'],
            'slug' => $slug,
            'season_number' => (int) ($validated['season_number'] ?? 1),
            'episode_number' => (int) ($validated['episode_number'] ?? 1),
            'excerpt' => $validated['excerpt'] ?? null,
            'description' => $validated['description'] ?? null,
            'source_type' => $validated['source_type'],
            'youtube_url' => $validated['youtube_url'] ?? null,
            'youtube_video_id' => $youtubeId,
            'video_file_path' => $videoFilePath,
            'thumbnail_url' => $validated['thumbnail_url'] ?? null,
            'duration_label' => $validated['duration_label'] ?? '30:00',
            'guest_name' => $validated['guest_name'] ?? null,
            'guest_role' => $validated['guest_role'] ?? null,
            'host_name' => $validated['host_name'] ?? 'Rédaction CIOMAG',
            'category_id' => $validated['category_id'] ?: null,
            'status' => $validated['status'],
            'is_featured' => $request->boolean('is_featured'),
            'published_at' => $validated['published_at'] ?? $podcast->published_at,
            'social_distribution' => [
                'youtube' => ['enabled' => $request->boolean('dist_youtube', true), 'status' => 'ready'],
                'facebook' => ['enabled' => $request->boolean('dist_facebook', true), 'status' => 'ready'],
                'linkedin' => ['enabled' => $request->boolean('dist_linkedin', true), 'status' => 'ready'],
                'twitter' => ['enabled' => $request->boolean('dist_twitter', true), 'status' => 'ready'],
            ],
        ]);

        return redirect()->route('admin.podcasts.index')
            ->with('success', 'Épisode de podcast mis à jour.');
    }

    public function destroy(Podcast $podcast): RedirectResponse
    {
        $podcast->delete();

        return redirect()->route('admin.podcasts.index')
            ->with('success', 'Épisode supprimé.');
    }

    private function validatePodcast(Request $request, ?Podcast $podcast = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('podcasts', 'slug')->ignore($podcast?->id),
            ],
            'season_number' => ['nullable', 'integer', 'min:1'],
            'episode_number' => ['nullable', 'integer', 'min:1'],
            'excerpt' => ['nullable', 'string', 'max:1000'],
            'description' => ['nullable', 'string'],
            'source_type' => ['required', 'in:youtube,upload'],
            'youtube_url' => ['nullable', 'string', 'max:500'],
            'video_file_path' => ['nullable', 'string', 'max:1000'],
            'video_file' => ['nullable', 'file', 'max:204800'],
            'thumbnail_url' => ['nullable', 'string', 'max:1000'],
            'duration_label' => ['nullable', 'string', 'max:40'],
            'guest_name' => ['nullable', 'string', 'max:150'],
            'guest_role' => ['nullable', 'string', 'max:200'],
            'host_name' => ['nullable', 'string', 'max:150'],
            'category_id' => ['nullable', 'uuid', 'exists:categories,id'],
            'status' => ['required', 'in:draft,published,archived'],
            'published_at' => ['nullable', 'date'],
        ]);
    }
}
