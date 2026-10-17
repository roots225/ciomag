@extends('layouts.admin')

@section('title', 'Bibliothèque de Médias & Studio de Recadrage')
@section('subtitle', 'Téléversement, édition des métadonnées SEO et recadrage interactif (Cropper.js)')

@section('content')
    <div class="space-y-6" @ciomag-media-updated.window="window.location.reload()">

        <!-- Barre d'actions rapides vers le composant universel -->
        <div class="admin-card p-5 sm:p-6 flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-lg bg-red-50 text-red-700 flex items-center justify-center shrink-0">
                    <i data-lucide="crop" class="w-5 h-5"></i>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">Studio Universel de Médias & Recadrage</h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Recadrez (16:9, 4:3, 1:1, libre), pivotez ou ajustez les métadonnées de vos visuels.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" onclick="window.openMediaManager && window.openMediaManager({ tab: 'upload' })"
                    class="btn btn-sm btn-primary">
                    <i data-lucide="upload-cloud" class="w-4 h-4"></i>
                    <span>Téléverser</span>
                </button>

                <button type="button" onclick="window.openMediaManager && window.openMediaManager({ tab: 'library' })"
                    class="btn btn-sm btn-secondary">
                    <i data-lucide="sliders" class="w-4 h-4"></i>
                    <span>Ouvrir la Médiathèque</span>
                </button>
            </div>
        </div>

        <!-- Grille des Médias -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 2xl:grid-cols-6 gap-4">
            @foreach($mediaItems as $media)
                <div class="admin-card overflow-hidden flex flex-col justify-between hover:shadow-xs transition group">
                    <div>
                        <div class="h-40 bg-slate-100 overflow-hidden relative">
                            @if($media->isImage())
                                <img src="{{ $media->url('thumbnail') }}" alt="{{ $media->alt_text }}"
                                    class="w-full h-full object-cover group-hover:scale-102 transition duration-300">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-xs font-mono text-slate-500">
                                    {{ $media->mime_type }}
                                </div>
                            @endif
                            <span
                                class="absolute bottom-2 right-2 px-1.5 py-0.5 text-[10px] font-medium bg-slate-900/80 text-white rounded">
                                {{ $media->humanFileSize() }}
                            </span>
                        </div>
                        <div class="p-3.5 space-y-1">
                            <div class="font-semibold text-xs text-slate-900 truncate" title="{{ $media->file_name }}">
                                {{ $media->file_name }}</div>
                            <div class="text-[11px] text-slate-400">
                                {{ $media->width ? $media->width . '×' . $media->height . 'px • ' : '' }}{{ $media->mime_type }}
                            </div>
                            @if($media->alt_text)
                                <div class="text-xs text-slate-500 line-clamp-1">Alt : {{ $media->alt_text }}</div>
                            @endif
                        </div>
                    </div>

                    <div class="px-3.5 py-2.5 border-t border-slate-100 flex items-center justify-between gap-1 text-xs">
                        <div class="flex items-center gap-1">
                            <button type="button"
                                onclick="window.openMediaManager && window.openMediaManager({ mediaId: '{{ $media->id }}', openCrop: true })"
                                class="btn btn-sm btn-secondary text-xs py-1 px-2" title="Recadrer cette image">
                                <i data-lucide="crop" class="w-3 h-3 text-red-700"></i>
                                <span>Recadrer</span>
                            </button>

                            <button type="button"
                                onclick="window.openMediaManager && window.openMediaManager({ mediaId: '{{ $media->id }}', openCrop: false })"
                                class="btn btn-sm btn-ghost text-xs py-1 px-2 text-slate-600" title="Modifier Alt & Légende">
                                <i data-lucide="pen-line" class="w-3 h-3"></i>
                                <span>Infos</span>
                            </button>
                        </div>

                        <form action="{{ route('admin.media.destroy', $media) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="button"
                                onclick="window.confirmAction({
                                    title: 'Supprimer le média',
                                    message: 'Êtes-vous sûr de vouloir supprimer définitivement « {{ addslashes($media->file_name) }} » ? Cette action est irréversible.',
                                    confirmText: 'Supprimer définitivement',
                                    cancelText: 'Annuler',
                                    variant: 'danger',
                                    icon: 'trash-2',
                                    form: this.closest('form')
                                })"
                                class="p-1.5 text-rose-600 hover:bg-rose-50 rounded-lg cursor-pointer transition"
                                title="Supprimer">
                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
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