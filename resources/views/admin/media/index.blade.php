@extends('layouts.admin')

@section('title', 'Bibliothèque de Médias & Studio de Recadrage')
@section('subtitle', 'Téléversement, édition des métadonnées SEO et recadrage interactif (Cropper.js)')

@section('content')
<div class="space-y-8" @ciomag-media-updated.window="window.location.reload()">

    <!-- Barre d'actions rapides vers le composant universel -->
    <div class="bg-slate-900 text-white rounded-2xl p-6 flex flex-wrap items-center justify-between gap-4 shadow-sm">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-red-700 flex items-center justify-center shrink-0">
                <i data-lucide="crop" class="w-6 h-6 text-white"></i>
            </div>
            <div>
                <h2 class="text-base font-black">Studio Universel de Médias & Recadrage</h2>
                <p class="text-xs text-slate-300 mt-0.5">
                    Cliquez sur « Recadrer » sur n'importe quel visuel pour ouvrir l'éditeur Cropper.js (16:9, 4:3, 1:1, rotation, miroir).
                </p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button
                type="button"
                onclick="window.openMediaManager && window.openMediaManager({ tab: 'upload' })"
                class="px-4 py-2.5 bg-red-700 hover:bg-red-600 text-white text-xs font-bold uppercase tracking-wider rounded-xl inline-flex items-center gap-2 transition cursor-pointer"
            >
                <i data-lucide="upload-cloud" class="w-4 h-4"></i>
                <span>Téléverser / Importer</span>
            </button>

            <button
                type="button"
                onclick="window.openMediaManager && window.openMediaManager({ tab: 'library' })"
                class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold uppercase tracking-wider rounded-xl inline-flex items-center gap-2 transition cursor-pointer"
            >
                <i data-lucide="sliders" class="w-4 h-4"></i>
                <span>Ouvrir le Studio Complet</span>
            </button>
        </div>
    </div>

    <!-- Grille des Médias -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach($mediaItems as $media)
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden flex flex-col justify-between shadow-2xs hover:shadow-md transition group">
                <div>
                    <div class="h-44 bg-slate-100 overflow-hidden relative">
                        @if($media->isImage())
                            <img src="{{ $media->url('thumbnail') }}" alt="{{ $media->alt_text }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-xs font-mono text-slate-500">
                                {{ $media->mime_type }}
                            </div>
                        @endif
                        <span class="absolute bottom-2 right-2 px-2 py-0.5 text-[10px] font-bold bg-slate-900/80 text-white rounded-xs">
                            {{ $media->humanFileSize() }}
                        </span>
                    </div>
                    <div class="p-4 space-y-1">
                        <div class="font-bold text-xs text-slate-900 truncate" title="{{ $media->file_name }}">{{ $media->file_name }}</div>
                        <div class="text-[11px] text-slate-400">
                            {{ $media->width ? $media->width . '×' . $media->height . 'px • ' : '' }}{{ $media->mime_type }}
                        </div>
                        @if($media->alt_text)
                            <div class="text-xs text-slate-600 line-clamp-1">Alt : {{ $media->alt_text }}</div>
                        @endif
                    </div>
                </div>

                <div class="px-4 py-3 border-t border-slate-100 flex items-center justify-between gap-2 text-xs">
                    <div class="flex items-center gap-1.5">
                        <button
                            type="button"
                            onclick="window.openMediaManager && window.openMediaManager({ mediaId: '{{ $media->id }}', openCrop: true })"
                            class="px-2.5 py-1.5 bg-amber-100 hover:bg-amber-200 text-amber-900 font-bold rounded-lg inline-flex items-center gap-1 transition cursor-pointer"
                            title="Recadrer ou pivoter cette image"
                        >
                            <i data-lucide="crop" class="w-3.5 h-3.5"></i>
                            <span>Recadrer</span>
                        </button>

                        <button
                            type="button"
                            onclick="window.openMediaManager && window.openMediaManager({ mediaId: '{{ $media->id }}', openCrop: false })"
                            class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg inline-flex items-center gap-1 transition cursor-pointer"
                            title="Modifier Alt & Légende"
                        >
                            <i data-lucide="pen-line" class="w-3.5 h-3.5"></i>
                            <span>Infos</span>
                        </button>
                    </div>

                    <form action="{{ route('admin.media.destroy', $media) }}" method="POST" onsubmit="return confirm('Supprimer ce média ?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-1.5 text-rose-600 hover:bg-rose-50 rounded-lg cursor-pointer" title="Supprimer">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>

    <div>
        {{ $mediaItems->links() }}
    </div>

</div>
@endsection
