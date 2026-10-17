@extends('layouts.admin')

@section('title', 'Podcasts & Vidéos')
@section('header_title', 'Studio Podcasts & Émissions Vidéo')

@section('top_actions')
    <a href="{{ route('magazine.podcasts.index') }}" target="_blank" class="px-3.5 py-2 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition flex items-center gap-1.5">
        <i data-lucide="external-link" class="w-3.5 h-3.5 text-red-700"></i>
        <span>Voir le Studio public</span>
    </a>
    <a href="{{ route('admin.podcasts.create') }}" class="px-4 py-2 rounded-lg bg-red-700 hover:bg-red-800 text-white text-xs font-bold transition shadow-xs flex items-center gap-1.5">
        <i data-lucide="video" class="w-4 h-4"></i>
        <span>Nouvel épisode</span>
    </a>
@endsection

@section('content')
<div class="space-y-6">

    {{-- Statistiques & Filtres de source --}}
    <div class="flex flex-wrap items-center justify-between gap-4 bg-white rounded-xl border border-slate-200 p-4 shadow-2xs">
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.podcasts.index') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition {{ !$source ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Tous les épisodes ({{ \App\Models\Podcast::count() }})
            </a>
            <a href="{{ route('admin.podcasts.index', ['source' => 'youtube']) }}" class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition inline-flex items-center gap-1.5 {{ $source === 'youtube' ? 'bg-red-700 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                <i data-lucide="youtube" class="w-3.5 h-3.5"></i>
                <span>Source YouTube ({{ \App\Models\Podcast::where('source_type', 'youtube')->count() }})</span>
            </a>
            <a href="{{ route('admin.podcasts.index', ['source' => 'upload']) }}" class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition inline-flex items-center gap-1.5 {{ $source === 'upload' ? 'bg-indigo-700 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                <i data-lucide="upload-cloud" class="w-3.5 h-3.5"></i>
                <span>Vidéo Locale / Plateforme ({{ \App\Models\Podcast::where('source_type', 'upload')->count() }})</span>
            </a>
        </div>

        <a href="{{ route('admin.social.index') }}" class="text-xs font-bold text-red-700 hover:text-red-800 inline-flex items-center gap-1.5">
            <i data-lucide="share-2" class="w-3.5 h-3.5"></i>
            <span>Hub multi-diffusion API (YouTube, Facebook, LinkedIn, X)</span>
        </a>
    </div>

    {{-- Tableau des épisodes --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="py-3.5 px-4">Épisode</th>
                        <th class="py-3.5 px-4">Source Vidéo</th>
                        <th class="py-3.5 px-4">Invité & Rubrique</th>
                        <th class="py-3.5 px-4">Durée & Vues</th>
                        <th class="py-3.5 px-4">Canaux API Prêts</th>
                        <th class="py-3.5 px-4">Statut</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($podcasts as $podcast)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $podcast->resolvedThumbnail() }}" alt="{{ $podcast->title }}" class="w-16 h-10 rounded-lg object-cover border border-slate-200 shrink-0">
                                    <div>
                                        <div class="flex items-center gap-1.5">
                                            <span class="px-1.5 py-0.5 rounded bg-slate-900 text-white text-[10px] font-extrabold">
                                                S{{ str_pad((string) $podcast->season_number, 2, '0', STR_PAD_LEFT) }}E{{ str_pad((string) $podcast->episode_number, 2, '0', STR_PAD_LEFT) }}
                                            </span>
                                            <span class="font-bold text-slate-900 line-clamp-1">{{ $podcast->title }}</span>
                                            @if($podcast->is_featured)
                                                <span class="px-1.5 py-0.5 rounded bg-amber-100 text-amber-800 text-[10px] font-extrabold">À la Une</span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-slate-400 mt-0.5">Animé par {{ $podcast->host_name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($podcast->source_type === 'youtube')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-red-50 text-red-700 text-xs font-bold border border-red-200">
                                        <i data-lucide="youtube" class="w-3.5 h-3.5"></i>
                                        <span>YouTube ({{ $podcast->youtube_video_id ?: 'ID auto' }})</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 text-xs font-bold border border-indigo-200">
                                        <i data-lucide="hard-drive" class="w-3.5 h-3.5"></i>
                                        <span>Vidéo Hébergée</span>
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-xs">
                                <div class="font-bold text-slate-800">{{ $podcast->guest_name ?: 'Émission spéciale' }}</div>
                                <div class="text-slate-500">{{ $podcast->guest_role ?: ($podcast->category?->name ?? 'Tech & DSI') }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-xs">
                                <div class="font-bold text-slate-800 flex items-center gap-1">
                                    <i data-lucide="clock" class="w-3 h-3 text-slate-400"></i>
                                    <span>{{ $podcast->duration_label }}</span>
                                </div>
                                <div class="text-slate-400">{{ number_format($podcast->view_count ?? 0, 0, ',', ' ') }} écoutes</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-1">
                                    @php $dist = $podcast->social_distribution ?? []; @endphp
                                    @foreach(['youtube' => 'youtube', 'facebook' => 'facebook', 'linkedin' => 'linkedin', 'twitter' => 'twitter'] as $net => $icon)
                                        <span
                                            class="w-6 h-6 rounded-md flex items-center justify-center text-xs {{ !empty($dist[$net]['enabled']) ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-300' }}"
                                            title="Distribution API {{ ucfirst($net) }}"
                                        >
                                            <i data-lucide="{{ $icon }}" class="w-3.5 h-3.5"></i>
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($podcast->status === 'published')
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Publié</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600">{{ ucfirst($podcast->status) }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="inline-flex items-center gap-1">
                                    <a href="{{ route('magazine.podcasts.show', $podcast->slug) }}" target="_blank" class="p-1.5 rounded-lg text-slate-500 hover:text-red-700 hover:bg-red-50 transition" title="Écouter / Regarder">
                                        <i data-lucide="play-circle" class="w-4 h-4"></i>
                                    </a>
                                    <a href="{{ route('admin.podcasts.edit', $podcast) }}" class="p-1.5 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition" title="Modifier">
                                        <i data-lucide="pen-square" class="w-4 h-4"></i>
                                    </a>
                                    <form action="{{ route('admin.podcasts.destroy', $podcast) }}" method="POST" onsubmit="return confirm('Supprimer cet épisode ?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer" title="Supprimer">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-slate-400">Aucun épisode enregistré.</td>
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
