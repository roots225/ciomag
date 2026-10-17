@extends('layouts.admin')

@section('title', 'Podcasts & Vidéos')
@section('header_title', 'Studio Podcasts & Émissions Vidéo')

@section('top_actions')
    <div class="flex items-center gap-2">
        <a href="{{ route('magazine.podcasts.index') }}" target="_blank" class="btn btn-sm btn-secondary">
            <i data-lucide="external-link" class="w-3.5 h-3.5 text-slate-500"></i>
            <span>Studio public</span>
        </a>
        <a href="{{ route('admin.podcasts.create') }}" class="btn btn-sm btn-primary">
            <i data-lucide="video" class="w-4 h-4"></i>
            <span>Nouvel épisode</span>
        </a>
    </div>
@endsection

@section('content')
<div class="space-y-6">

    {{-- Statistiques & Filtres de source --}}
    <div class="flex flex-wrap items-center justify-between gap-3 admin-card p-4">
        <div class="flex flex-wrap items-center gap-1.5">
            <a href="{{ route('admin.podcasts.index') }}" class="px-3 py-1.5 rounded-lg text-xs font-medium transition {{ !$source ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Tous les épisodes ({{ \App\Models\Podcast::count() }})
            </a>
            <a href="{{ route('admin.podcasts.index', ['source' => 'youtube']) }}" class="px-3 py-1.5 rounded-lg text-xs font-medium transition inline-flex items-center gap-1.5 {{ $source === 'youtube' ? 'bg-red-700 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                <i data-lucide="youtube" class="w-3.5 h-3.5"></i>
                <span>Source YouTube ({{ \App\Models\Podcast::where('source_type', 'youtube')->count() }})</span>
            </a>
            <a href="{{ route('admin.podcasts.index', ['source' => 'upload']) }}" class="px-3 py-1.5 rounded-lg text-xs font-medium transition inline-flex items-center gap-1.5 {{ $source === 'upload' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                <i data-lucide="upload-cloud" class="w-3.5 h-3.5"></i>
                <span>Vidéo Hébergée ({{ \App\Models\Podcast::where('source_type', 'upload')->count() }})</span>
            </a>
        </div>

        <a href="{{ route('admin.social.index') }}" class="text-xs font-medium text-red-700 hover:underline inline-flex items-center gap-1.5">
            <i data-lucide="share-2" class="w-3.5 h-3.5"></i>
            <span>Hub multi-diffusion API</span>
        </a>
    </div>

    {{-- Tableau des épisodes --}}
    <div class="admin-table-container">
        <div class="overflow-x-auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Épisode</th>
                        <th>Source Vidéo</th>
                        <th>Invité & Rubrique</th>
                        <th>Durée & Vues</th>
                        <th>Canaux API</th>
                        <th>Statut</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($podcasts as $podcast)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td>
                                <div class="flex items-center gap-3">
                                    <img src="{{ $podcast->resolvedThumbnail() }}" alt="{{ $podcast->title }}" class="w-14 h-9 rounded-md object-cover border border-slate-200 shrink-0">
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5">
                                            <span class="px-1.5 py-0.5 rounded bg-slate-900 text-white text-[10px] font-bold">
                                                S{{ str_pad((string) $podcast->season_number, 2, '0', STR_PAD_LEFT) }}E{{ str_pad((string) $podcast->episode_number, 2, '0', STR_PAD_LEFT) }}
                                            </span>
                                            <span class="font-semibold text-xs text-slate-900 line-clamp-1">{{ $podcast->title }}</span>
                                            @if($podcast->is_featured)
                                                <span class="admin-badge badge-warning text-[9px] py-0 px-1.5">À la Une</span>
                                            @endif
                                        </div>
                                        <div class="text-[11px] text-slate-400 mt-0.5">Animé par {{ $podcast->host_name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($podcast->source_type === 'youtube')
                                    <span class="admin-badge badge-danger inline-flex items-center gap-1">
                                        <i data-lucide="youtube" class="w-3 h-3"></i>
                                        <span>YouTube</span>
                                    </span>
                                @else
                                    <span class="admin-badge badge-neutral inline-flex items-center gap-1">
                                        <i data-lucide="hard-drive" class="w-3 h-3"></i>
                                        <span>Hébergée</span>
                                    </span>
                                @endif
                            </td>
                            <td class="text-xs">
                                <div class="font-medium text-slate-800">{{ $podcast->guest_name ?: 'Émission spéciale' }}</div>
                                <div class="text-slate-400 text-[11px]">{{ $podcast->guest_role ?: ($podcast->category?->name ?? 'Tech & DSI') }}</div>
                            </td>
                            <td class="text-xs">
                                <div class="font-medium text-slate-800 flex items-center gap-1">
                                    <i data-lucide="clock" class="w-3 h-3 text-slate-400"></i>
                                    <span>{{ $podcast->duration_label }}</span>
                                </div>
                                <div class="text-slate-400 text-[11px]">{{ number_format($podcast->view_count ?? 0, 0, ',', ' ') }} écoutes</div>
                            </td>
                            <td>
                                <div class="flex items-center gap-1">
                                    @php $dist = $podcast->social_distribution ?? []; @endphp
                                    @foreach(['youtube' => 'youtube', 'facebook' => 'facebook', 'linkedin' => 'linkedin', 'twitter' => 'twitter'] as $net => $icon)
                                        <span
                                            class="w-5 h-5 rounded flex items-center justify-center text-xs {{ !empty($dist[$net]['enabled']) ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-300' }}"
                                            title="API {{ ucfirst($net) }}"
                                        >
                                            <i data-lucide="{{ $icon }}" class="w-3 h-3"></i>
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                @if($podcast->status === 'published')
                                    <span class="admin-badge badge-success">Publié</span>
                                @else
                                    <span class="admin-badge badge-neutral">{{ ucfirst($podcast->status) }}</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="inline-flex items-center justify-end gap-1">
                                    <a href="{{ route('magazine.podcasts.show', $podcast->slug) }}" target="_blank" class="p-1.5 text-slate-400 hover:text-red-700 rounded-lg transition" title="Écouter / Regarder">
                                        <i data-lucide="play-circle" class="w-4 h-4"></i>
                                    </a>
                                    <a href="{{ route('admin.podcasts.edit', $podcast) }}" class="p-1.5 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition" title="Modifier">
                                        <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                    </a>
                                    <form action="{{ route('admin.podcasts.destroy', $podcast) }}" method="POST" onsubmit="return confirm('Supprimer cet épisode ?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer" title="Supprimer">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-slate-400 text-xs">Aucun épisode enregistré.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $podcasts->links() }}
        </div>
    </div>

</div>
@endsection
