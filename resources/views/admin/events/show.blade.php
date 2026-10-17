@extends('layouts.admin')

@section('title', 'Détail Événement : ' . $event->title)
@section('header_title', $event->title)
@section('subtitle', $event->event_type . ' • ' . $event->locationLabel() . ' • ' . ($event->starts_at?->translatedFormat('d F Y à H:i') ?? ''))

@section('top_actions')
    <a href="{{ route('admin.events.index') }}" class="px-3.5 py-2 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition inline-flex items-center gap-1.5">
        <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
        <span>Tous les événements</span>
    </a>
    <a href="{{ route('magazine.events.show', $event->slug) }}" target="_blank" class="px-3.5 py-2 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition inline-flex items-center gap-1.5">
        <i data-lucide="external-link" class="w-3.5 h-3.5 text-red-700"></i>
        <span>Page publique</span>
    </a>
    <a href="{{ route('admin.events.edit', $event) }}" class="px-4 py-2 rounded-lg bg-slate-900 hover:bg-red-700 text-white text-xs font-bold transition inline-flex items-center gap-1.5">
        <i data-lucide="pen-square" class="w-3.5 h-3.5"></i>
        <span>Modifier l'événement</span>
    </a>
@endsection

@section('content')
<div x-data="{ openAddParticipantModal: false, activeTab: 'participants' }" class="space-y-8">

    {{-- Bandeau Récapitulatif de l'Événement --}}
    <div class="bg-slate-950 text-white rounded-2xl border border-slate-800 overflow-hidden shadow-sm relative">
        @if($event->cover_url)
            <img src="{{ $event->cover_url }}" alt="{{ $event->title }}" class="absolute inset-0 w-full h-full object-cover opacity-20">
        @endif
        <div class="absolute inset-0 bg-gradient-to-r from-slate-950 via-slate-950/90 to-slate-950/70"></div>

        <div class="relative z-10 p-6 sm:p-8 grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">
            <div class="lg:col-span-8 space-y-3">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-2.5 py-1 rounded-full bg-red-700 text-white text-[11px] font-extrabold uppercase tracking-wider">
                        {{ $event->event_type }}
                    </span>
                    <span class="px-2.5 py-1 rounded-full bg-amber-400 text-slate-950 text-[11px] font-extrabold uppercase">
                        {{ $event->formattedPrice() }}
                    </span>
                    @if($event->starts_at && $event->starts_at->isFuture())
                        <span class="px-2.5 py-1 rounded-full bg-emerald-500/20 border border-emerald-400/40 text-emerald-300 text-[11px] font-bold inline-flex items-center gap-1">
                            <i data-lucide="timer" class="w-3.5 h-3.5"></i>
                            <span>Décompte : J-{{ (int) now()->diffInDays($event->starts_at) }}</span>
                        </span>
                    @endif
                </div>

                <h2 class="text-2xl sm:text-3xl font-black text-white leading-tight">{{ $event->title }}</h2>
                @if($event->subtitle)
                    <p class="text-sm text-amber-400 font-semibold">{{ $event->subtitle }}</p>
                @endif
                @if($event->excerpt)
                    <p class="text-xs text-slate-300 max-w-3xl leading-relaxed">{{ $event->excerpt }}</p>
                @endif

                <div class="pt-2 flex flex-wrap items-center gap-4 text-xs text-slate-300">
                    <span class="inline-flex items-center gap-1.5">
                        <i data-lucide="calendar" class="w-4 h-4 text-amber-400"></i>
                        <span>Du <strong>{{ $event->starts_at?->translatedFormat('d M Y H:i') }}</strong> {{ $event->ends_at ? 'au ' . $event->ends_at->translatedFormat('d M Y H:i') : '' }}</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <i data-lucide="map-pin" class="w-4 h-4 text-red-500"></i>
                        <span>{{ $event->locationLabel() }}</span>
                    </span>
                </div>
            </div>

            <div class="lg:col-span-4 bg-white/5 border border-white/10 rounded-2xl p-5 space-y-4">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-400 font-bold uppercase tracking-wider">Taux de remplissage</span>
                    <span class="font-black text-amber-400">{{ $event->confirmed_registrations_count }} / {{ $event->capacity }} places</span>
                </div>
                @php
                    $fillPercent = $event->capacity > 0 ? min(100, round(($event->confirmed_registrations_count / $event->capacity) * 100)) : 0;
                @endphp
                <div class="w-full h-2.5 bg-slate-800 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-red-600 to-amber-400 rounded-full" style="width: {{ $fillPercent }}%"></div>
                </div>

                <div class="grid grid-cols-3 gap-2 pt-1 text-center">
                    <div class="bg-slate-900/90 rounded-xl p-2.5 border border-slate-800">
                        <span class="block text-lg font-black text-white">{{ $event->registrations_count }}</span>
                        <span class="text-[10px] text-slate-400 uppercase font-bold">Total Inscrits</span>
                    </div>
                    <div class="bg-slate-900/90 rounded-xl p-2.5 border border-slate-800">
                        <span class="block text-lg font-black text-emerald-400">{{ $event->confirmed_registrations_count }}</span>
                        <span class="text-[10px] text-slate-400 uppercase font-bold">Confirmés</span>
                    </div>
                    <div class="bg-slate-900/90 rounded-xl p-2.5 border border-slate-800">
                        <span class="block text-lg font-black text-amber-400">{{ count($event->program ?? []) }}</span>
                        <span class="text-[10px] text-slate-400 uppercase font-bold">Sessions</span>
                    </div>
                </div>

                <button
                    type="button"
                    @click="openAddParticipantModal = true"
                    class="w-full py-2.5 px-4 rounded-xl bg-red-700 hover:bg-red-800 text-white text-xs font-bold uppercase tracking-wider transition flex items-center justify-center gap-2 cursor-pointer"
                >
                    <i data-lucide="user-plus" class="w-4 h-4"></i>
                    <span>Inscrire un participant</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Navigation par Onglets : Participants Inscrits vs Détails Complets (Programme, Comité, Partenaires) --}}
    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 pb-3">
        <div class="flex flex-wrap items-center gap-2">
            <button
                type="button"
                @click="activeTab = 'participants'"
                :class="activeTab === 'participants' ? 'bg-slate-900 text-white shadow-xs' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50'"
                class="px-4 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider transition inline-flex items-center gap-2 cursor-pointer"
            >
                <i data-lucide="users" class="w-4 h-4 text-red-500"></i>
                <span>Participants inscrits ({{ $event->registrations_count }})</span>
            </button>

            <button
                type="button"
                @click="activeTab = 'details'"
                :class="activeTab === 'details' ? 'bg-slate-900 text-white shadow-xs' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50'"
                class="px-4 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider transition inline-flex items-center gap-2 cursor-pointer"
            >
                <i data-lucide="layout-list" class="w-4 h-4 text-amber-500"></i>
                <span>Programme ({{ count($event->program ?? []) }}), Comité ({{ count($event->committee ?? []) }}) & Partenaires ({{ count($event->partners ?? []) }})</span>
            </button>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.events.edit', $event) }}" class="px-3.5 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-800 text-xs font-bold inline-flex items-center gap-1.5">
                <i data-lucide="pen-square" class="w-3.5 h-3.5 text-red-700"></i>
                <span>Éditer le programme / comité / partenaires</span>
            </a>
        </div>
    </div>

    {{-- ONGLET 1 : LISTE DES PARTICIPANTS INSCRITS À CET ÉVÉNEMENT --}}
    <div x-show="activeTab === 'participants'" class="space-y-6">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex flex-wrap items-center justify-between gap-4">
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('admin.events.show', $event) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold {{ !$status ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                        Tous ({{ $event->registrations_count }})
                    </a>
                    <a href="{{ route('admin.events.show', [$event, 'status' => 'confirmed']) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold {{ $status === 'confirmed' ? 'bg-emerald-700 text-white' : 'bg-emerald-50 text-emerald-800 hover:bg-emerald-100' }}">
                        Confirmés
                    </a>
                    <a href="{{ route('admin.events.show', [$event, 'status' => 'pending']) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold {{ $status === 'pending' ? 'bg-amber-600 text-white' : 'bg-amber-50 text-amber-800 hover:bg-amber-100' }}">
                        En attente
                    </a>
                    <a href="{{ route('admin.events.show', [$event, 'status' => 'cancelled']) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold {{ $status === 'cancelled' ? 'bg-rose-700 text-white' : 'bg-rose-50 text-rose-800 hover:bg-rose-100' }}">
                        Annulés
                    </a>
                </div>

                <form action="{{ route('admin.events.show', $event) }}" method="GET" class="flex items-center gap-2">
                    @if($status)
                        <input type="hidden" name="status" value="{{ $status }}">
                    @endif
                    <div class="relative">
                        <input
                            type="text"
                            name="q"
                            value="{{ $search }}"
                            placeholder="Rechercher nom, email, badge, société..."
                            class="w-64 pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-300 focus:border-red-700 focus:outline-none"
                        >
                        <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                    </div>
                    <button type="submit" class="px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-red-700 text-white text-xs font-bold transition cursor-pointer">
                        Filtrer
                    </button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="py-3.5 px-4">Code Badge</th>
                            <th class="py-3.5 px-4">Participant</th>
                            <th class="py-3.5 px-4">Entreprise & Fonction</th>
                            <th class="py-3.5 px-4">Format</th>
                            <th class="py-3.5 px-4">Date d'inscription</th>
                            <th class="py-3.5 px-4">Statut</th>
                            <th class="py-3.5 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse($registrations as $reg)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 px-4 font-mono text-xs font-bold text-red-700">
                                    {{ $reg->ticket_code }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900">{{ $reg->full_name }}</div>
                                    <div class="text-xs text-slate-500">{{ $reg->email }} {{ $reg->phone ? '• ' . $reg->phone : '' }}</div>
                                </td>
                                <td class="py-3.5 px-4 text-xs">
                                    <div class="font-semibold text-slate-800">{{ $reg->company ?: '—' }}</div>
                                    <div class="text-slate-500">{{ $reg->job_title ?: 'Participant' }}{{ $reg->country ? ' (' . $reg->country . ')' : '' }}</div>
                                </td>
                                <td class="py-3.5 px-4 text-xs">
                                    @if($reg->attendance_mode === 'online')
                                        <span class="px-2.5 py-1 rounded-full bg-sky-50 text-sky-700 font-bold text-[11px]">En ligne</span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full bg-purple-50 text-purple-700 font-bold text-[11px]">Présentiel</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-xs text-slate-500">
                                    {{ $reg->created_at?->format('d/m/Y H:i') }}
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($reg->status === 'confirmed')
                                        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Confirmé</span>
                                    @elseif($reg->status === 'pending')
                                        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">En attente</span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">Annulé</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        <form action="{{ route('admin.events.registrations.status', $reg) }}" method="POST" class="inline-flex items-center gap-1.5">
                                            @csrf
                                            @method('PATCH')
                                            <div class="w-36 text-left">
                                                <x-ui.select
                                                    name="status"
                                                    :value="$reg->status"
                                                    :options="[
                                                        ['value' => 'confirmed', 'label' => 'Confirmé'],
                                                        ['value' => 'pending', 'label' => 'En attente'],
                                                        ['value' => 'cancelled', 'label' => 'Annulé'],
                                                    ]"
                                                />
                                            </div>
                                            <button type="submit" class="p-2 rounded-lg bg-slate-900 hover:bg-red-700 text-white transition cursor-pointer" title="Enregistrer le statut">
                                                <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                            </button>
                                        </form>

                                        <form action="{{ route('admin.events.registrations.destroy', $reg) }}" method="POST" onsubmit="return confirm('Supprimer cette inscription ?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer" title="Supprimer">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-10 text-center text-slate-400">
                                    Aucun participant inscrit pour ce filtre.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100">
                {{ $registrations->links() }}
            </div>
        </div>
    </div>

    {{-- ONGLET 2 : DÉTAILS DE L'ÉVÉNEMENT (PRÉSENTATION, PROGRAMME, COMITÉ, PARTENAIRES) --}}
    <div x-show="activeTab === 'details'" style="display: none;" class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-7 space-y-6">
            {{-- Présentation --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-2xs space-y-3">
                <h3 class="text-sm font-black uppercase tracking-wider text-slate-900 flex items-center gap-2 border-b border-slate-100 pb-3">
                    <i data-lucide="file-text" class="w-4 h-4 text-red-700"></i>
                    <span>Présentation de l'Événement</span>
                </h3>
                <div class="prose prose-sm max-w-none text-slate-700">
                    {!! $event->description ?: '<p class="text-slate-400">Aucune description détaillée.</p>' !!}
                </div>
            </div>

            {{-- Programme Horaire --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-2xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-black uppercase tracking-wider text-slate-900 flex items-center gap-2">
                        <i data-lucide="calendar-range" class="w-4 h-4 text-red-700"></i>
                        <span>Programme Officiel ({{ count($event->program ?? []) }} sessions)</span>
                    </h3>
                    <a href="{{ route('admin.events.edit', $event) }}" class="text-xs font-bold text-red-700 hover:underline">Modifier le programme</a>
                </div>

                <div class="space-y-3">
                    @forelse($event->program ?? [] as $slot)
                        <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/70 space-y-1.5">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <span class="px-2.5 py-0.5 rounded bg-red-700 text-white text-xs font-bold">{{ $slot['time'] ?? '' }}</span>
                                <span class="text-xs font-bold text-slate-500">{{ $slot['role'] ?? '' }} • {{ $slot['track'] ?? '' }}</span>
                            </div>
                            <div class="font-bold text-slate-900 text-sm">{{ $slot['title'] ?? '' }}</div>
                            @if(!empty($slot['speaker']))
                                <div class="text-xs text-red-700 font-semibold">Intervenant(s) : {{ $slot['speaker'] }}</div>
                            @endif
                            @if(!empty($slot['description']))
                                <p class="text-xs text-slate-600">{{ $slot['description'] }}</p>
                            @endif
                        </div>
                    @empty
                        <p class="text-xs text-slate-400">Aucune session programmée.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="lg:col-span-5 space-y-6">
            {{-- Comité d'Organisation --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-2xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-black uppercase tracking-wider text-slate-900 flex items-center gap-2">
                        <i data-lucide="users" class="w-4 h-4 text-red-700"></i>
                        <span>Comité d'Organisation ({{ count($event->committee ?? []) }})</span>
                    </h3>
                </div>

                <div class="space-y-3">
                    @forelse($event->committee ?? [] as $member)
                        <div class="p-3.5 rounded-xl border border-slate-200 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                @if(!empty($member['photo_url']))
                                    <img src="{{ $member['photo_url'] }}" alt="{{ $member['name'] ?? '' }}" class="w-12 h-12 rounded-full object-cover border border-slate-200 shrink-0">
                                @else
                                    <div class="w-12 h-12 rounded-full bg-slate-900 text-white flex items-center justify-center font-bold text-xs shrink-0">
                                        CIO
                                    </div>
                                @endif
                                <div>
                                    <div class="font-bold text-slate-900 text-sm">{{ $member['name'] ?? '' }}</div>
                                    <div class="text-xs text-red-700 font-semibold">{{ $member['role'] ?? '' }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $member['company'] ?? '' }}</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5">
                                @if(!empty($member['linkedin']))
                                    <a href="{{ $member['linkedin'] }}" target="_blank" class="p-1.5 rounded-lg bg-slate-100 hover:bg-blue-50 text-blue-700">
                                        <i data-lucide="linkedin" class="w-3.5 h-3.5"></i>
                                    </a>
                                @endif
                                @if(!empty($member['twitter']))
                                    <a href="{{ $member['twitter'] }}" target="_blank" class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800">
                                        <i data-lucide="twitter" class="w-3.5 h-3.5"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400">Aucun membre renseigné.</p>
                    @endforelse
                </div>
            </div>

            {{-- Logos des Partenaires --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-2xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-black uppercase tracking-wider text-slate-900 flex items-center gap-2">
                        <i data-lucide="handshake" class="w-4 h-4 text-red-700"></i>
                        <span>Partenaires & Sponsors ({{ count($event->partners ?? []) }})</span>
                    </h3>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    @forelse($event->partners ?? [] as $partner)
                        <div class="p-3 rounded-xl border border-slate-200 bg-slate-50 flex flex-col items-center text-center gap-2">
                            @if(!empty($partner['logo_url']))
                                <img src="{{ $partner['logo_url'] }}" alt="{{ $partner['name'] ?? '' }}" class="h-10 object-contain">
                            @endif
                            <div class="text-xs font-bold text-slate-900">{{ $partner['name'] ?? '' }}</div>
                            <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[10px] font-bold">{{ $partner['tier'] ?? 'Partenaire' }}</span>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 col-span-2">Aucun partenaire renseigné.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- DIALOG MODAL : INSCRIRE MANUELLEMENT UN PARTICIPANT --}}
    <div
        x-show="openAddParticipantModal"
        style="display: none;"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 backdrop-blur-xs p-4"
        @keydown.escape.window="openAddParticipantModal = false"
    >
        <div @click.outside="openAddParticipantModal = false" class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-lg w-full overflow-hidden">
            <div class="px-6 py-4 bg-slate-950 text-white flex items-center justify-between">
                <h3 class="text-sm font-black uppercase tracking-wider flex items-center gap-2">
                    <i data-lucide="user-plus" class="w-4 h-4 text-red-500"></i>
                    <span>Inscrire un participant à l'événement</span>
                </h3>
                <button type="button" @click="openAddParticipantModal = false" class="text-slate-400 hover:text-white cursor-pointer">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('admin.events.registrations.store', $event) }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nom complet *</label>
                        <input type="text" name="full_name" required placeholder="Ex: Dr. Seydou Koné" class="w-full px-3.5 py-2 text-sm border border-slate-300 rounded-xl">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Email professionnel *</label>
                        <input type="email" name="email" required placeholder="s.kone@entreprise.com" class="w-full px-3.5 py-2 text-sm border border-slate-300 rounded-xl">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Entreprise / Institution</label>
                        <input type="text" name="company" placeholder="Ex: Groupe Banque Atlantique" class="w-full px-3.5 py-2 text-sm border border-slate-300 rounded-xl">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Fonction / Poste</label>
                        <input type="text" name="job_title" placeholder="Ex: DSI Groupe" class="w-full px-3.5 py-2 text-sm border border-slate-300 rounded-xl">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Téléphone</label>
                        <input type="text" name="phone" placeholder="+225 07 00 00 00" class="w-full px-3.5 py-2 text-sm border border-slate-300 rounded-xl">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Pays</label>
                        <input type="text" name="country" placeholder="Côte d'Ivoire" class="w-full px-3.5 py-2 text-sm border border-slate-300 rounded-xl">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <x-ui.select
                        name="attendance_mode"
                        label="Mode de participation"
                        value="in_person"
                        :options="[
                            ['value' => 'in_person', 'label' => 'Présentiel (Badge VIP)'],
                            ['value' => 'online', 'label' => 'Distanciel (Streaming)'],
                        ]"
                    />
                    <x-ui.select
                        name="status"
                        label="Statut initial"
                        value="confirmed"
                        :options="[
                            ['value' => 'confirmed', 'label' => 'Confirmé immédiatement'],
                            ['value' => 'pending', 'label' => 'En attente de validation'],
                        ]"
                    />
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="openAddParticipantModal = false" class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-bold text-slate-700 hover:bg-slate-50 cursor-pointer">
                        Annuler
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-red-700 hover:bg-red-800 text-white text-xs font-bold uppercase tracking-wider cursor-pointer">
                        Générer l'accréditation
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
