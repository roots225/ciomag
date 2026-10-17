@extends('layouts.admin')

@section('title', 'Détail Événement : ' . $event->title)
@section('header_title', $event->title)
@section('subtitle', $event->event_type . ' • ' . $event->locationLabel() . ' • ' . ($event->starts_at?->translatedFormat('d F Y à H:i') ?? ''))

@section('top_actions')
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.events.index') }}" class="btn btn-sm btn-secondary">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
            <span>Tous les événements</span>
        </a>
        <a href="{{ route('magazine.events.show', $event->slug) }}" target="_blank" class="btn btn-sm btn-secondary">
            <i data-lucide="external-link" class="w-3.5 h-3.5 text-slate-500"></i>
            <span>Page publique</span>
        </a>
        <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-sm btn-primary">
            <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
            <span>Modifier l'événement</span>
        </a>
    </div>
@endsection

@section('content')
<div x-data="{ openAddParticipantModal: false, activeTab: 'participants' }" class="space-y-6">

    {{-- Bandeau Récapitulatif de l'Événement (Sobre & Structuré) --}}
    <div class="admin-card p-6 bg-white border border-slate-200">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <div class="lg:col-span-8 space-y-3">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="admin-badge badge-primary">
                        {{ $event->event_type }}
                    </span>
                    <span class="admin-badge badge-warning">
                        {{ $event->formattedPrice() }}
                    </span>
                    @if($event->starts_at && $event->starts_at->isFuture())
                        <span class="admin-badge badge-success inline-flex items-center gap-1">
                            <i data-lucide="timer" class="w-3 h-3"></i>
                            <span>Décompte : J-{{ (int) now()->diffInDays($event->starts_at) }}</span>
                        </span>
                    @endif
                </div>

                <h2 class="text-xl font-bold text-slate-900 leading-snug">{{ $event->title }}</h2>
                @if($event->subtitle)
                    <p class="text-xs font-medium text-slate-600">{{ $event->subtitle }}</p>
                @endif
                @if($event->excerpt)
                    <p class="text-xs text-slate-500 max-w-3xl leading-relaxed">{{ $event->excerpt }}</p>
                @endif

                <div class="pt-1 flex flex-wrap items-center gap-4 text-xs text-slate-500">
                    <span class="inline-flex items-center gap-1.5">
                        <i data-lucide="calendar" class="w-3.5 h-3.5 text-slate-400"></i>
                        <span>Du <strong class="text-slate-700">{{ $event->starts_at?->translatedFormat('d M Y H:i') }}</strong> {{ $event->ends_at ? 'au ' . $event->ends_at->translatedFormat('d M Y H:i') : '' }}</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <i data-lucide="map-pin" class="w-3.5 h-3.5 text-red-600"></i>
                        <span class="text-slate-700 font-medium">{{ $event->locationLabel() }}</span>
                    </span>
                </div>
            </div>

            <div class="lg:col-span-4 bg-slate-50 rounded-xl p-4 border border-slate-100 space-y-3">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-500 font-medium">Taux de remplissage</span>
                    <span class="font-bold text-slate-900">{{ $event->confirmed_registrations_count }} / {{ $event->capacity }} places</span>
                </div>
                @php
                    $fillPercent = $event->capacity > 0 ? min(100, round(($event->confirmed_registrations_count / $event->capacity) * 100)) : 0;
                @endphp
                <div class="w-full h-2 bg-slate-200 rounded-full overflow-hidden">
                    <div class="h-full bg-red-700 rounded-full" style="width: {{ $fillPercent }}%"></div>
                </div>

                <div class="grid grid-cols-3 gap-2 pt-1 text-center">
                    <div class="bg-white rounded-lg p-2 border border-slate-200">
                        <span class="block text-base font-bold text-slate-900">{{ $event->registrations_count }}</span>
                        <span class="text-[10px] text-slate-400 font-medium">Inscrits</span>
                    </div>
                    <div class="bg-white rounded-lg p-2 border border-slate-200">
                        <span class="block text-base font-bold text-emerald-600">{{ $event->confirmed_registrations_count }}</span>
                        <span class="text-[10px] text-slate-400 font-medium">Confirmés</span>
                    </div>
                    <div class="bg-white rounded-lg p-2 border border-slate-200">
                        <span class="block text-base font-bold text-amber-600">{{ count($event->program ?? []) }}</span>
                        <span class="text-[10px] text-slate-400 font-medium">Sessions</span>
                    </div>
                </div>

                <button
                    type="button"
                    @click="openAddParticipantModal = true"
                    class="w-full btn btn-sm btn-primary justify-center"
                >
                    <i data-lucide="user-plus" class="w-3.5 h-3.5"></i>
                    <span>Inscrire un participant</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Onglets : Participants Inscrits vs Programme / Comité / Partenaires --}}
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 pb-2">
        <div class="flex flex-wrap items-center gap-1.5">
            <button
                type="button"
                @click="activeTab = 'participants'"
                :class="activeTab === 'participants' ? 'bg-slate-900 text-white' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50'"
                class="px-3.5 py-1.5 rounded-lg text-xs font-medium transition inline-flex items-center gap-1.5 cursor-pointer"
            >
                <i data-lucide="users" class="w-3.5 h-3.5"></i>
                <span>Participants inscrits ({{ $event->registrations_count }})</span>
            </button>

            <button
                type="button"
                @click="activeTab = 'details'"
                :class="activeTab === 'details' ? 'bg-slate-900 text-white' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50'"
                class="px-3.5 py-1.5 rounded-lg text-xs font-medium transition inline-flex items-center gap-1.5 cursor-pointer"
            >
                <i data-lucide="layout-list" class="w-3.5 h-3.5"></i>
                <span>Programme, Comité & Partenaires</span>
            </button>
        </div>

        <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-sm btn-secondary">
            <i data-lucide="pencil" class="w-3.5 h-3.5 text-slate-500"></i>
            <span>Éditer le contenu de l'événement</span>
        </a>
    </div>

    {{-- ONGLET 1 : LISTE DES PARTICIPANTS INSCRITS --}}
    <div x-show="activeTab === 'participants'" class="space-y-6">
        <div class="admin-table-container">
            <div class="px-5 py-3.5 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
                <div class="flex flex-wrap items-center gap-1.5">
                    <a href="{{ route('admin.events.show', $event) }}" class="px-3 py-1 rounded-lg text-xs font-medium {{ !$status ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                        Tous ({{ $event->registrations_count }})
                    </a>
                    <a href="{{ route('admin.events.show', [$event, 'status' => 'confirmed']) }}" class="px-3 py-1 rounded-lg text-xs font-medium {{ $status === 'confirmed' ? 'bg-emerald-700 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                        Confirmés
                    </a>
                    <a href="{{ route('admin.events.show', [$event, 'status' => 'pending']) }}" class="px-3 py-1 rounded-lg text-xs font-medium {{ $status === 'pending' ? 'bg-amber-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                        En attente
                    </a>
                    <a href="{{ route('admin.events.show', [$event, 'status' => 'cancelled']) }}" class="px-3 py-1 rounded-lg text-xs font-medium {{ $status === 'cancelled' ? 'bg-rose-700 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                        Annulés
                    </a>
                </div>

                <form action="{{ route('admin.events.show', $event) }}" method="GET" class="flex items-center gap-2">
                    @if($status)
                        <input type="hidden" name="status" value="{{ $status }}">
                    @endif
                    <div class="relative w-60">
                        <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                        <input
                            type="text"
                            name="q"
                            value="{{ $search }}"
                            placeholder="Rechercher nom, email, badge..."
                            class="w-full pl-8 pr-3 py-1.5 text-xs rounded-lg border border-slate-200 focus:outline-none focus:border-slate-800"
                        >
                    </div>
                    <button type="submit" class="btn btn-sm btn-dark">
                        Filtrer
                    </button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Code Badge</th>
                            <th>Participant</th>
                            <th>Entreprise & Fonction</th>
                            <th>Format</th>
                            <th>Date d'inscription</th>
                            <th>Statut</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($registrations as $reg)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="font-mono text-xs font-semibold text-red-700">
                                    {{ $reg->ticket_code }}
                                </td>
                                <td class="text-xs">
                                    <div class="font-semibold text-slate-900">{{ $reg->full_name }}</div>
                                    <div class="text-slate-500">{{ $reg->email }} {{ $reg->phone ? '• ' . $reg->phone : '' }}</div>
                                </td>
                                <td class="text-xs">
                                    <div class="font-medium text-slate-800">{{ $reg->company ?: '—' }}</div>
                                    <div class="text-slate-400">{{ $reg->job_title ?: 'Participant' }}{{ $reg->country ? ' (' . $reg->country . ')' : '' }}</div>
                                </td>
                                <td class="text-xs">
                                    <span class="admin-badge {{ $reg->attendance_mode === 'online' ? 'badge-info' : 'badge-neutral' }}">
                                        {{ $reg->attendance_mode === 'online' ? 'En ligne' : 'Présentiel' }}
                                    </span>
                                </td>
                                <td class="text-xs text-slate-500">
                                    {{ $reg->created_at?->format('d/m/Y H:i') }}
                                </td>
                                <td>
                                    @php
                                        $regBadge = match($reg->status) {
                                            'confirmed' => 'badge-success',
                                            'pending' => 'badge-warning',
                                            default => 'badge-danger',
                                        };
                                        $regLabel = match($reg->status) {
                                            'confirmed' => 'Confirmé',
                                            'pending' => 'En attente',
                                            default => 'Annulé',
                                        };
                                    @endphp
                                    <span class="admin-badge {{ $regBadge }}">{{ $regLabel }}</span>
                                </td>
                                <td class="text-right">
                                    <div class="inline-flex items-center justify-end gap-1.5">
                                        <form action="{{ route('admin.events.registrations.status', $reg) }}" method="POST" class="inline-flex items-center gap-1.5">
                                            @csrf
                                            @method('PATCH')
                                            <div class="w-32 text-left">
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
                                            <button type="submit" class="btn btn-sm btn-dark" title="Enregistrer">
                                                <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                            </button>
                                        </form>

                                        <form action="{{ route('admin.events.registrations.destroy', $reg) }}" method="POST" onsubmit="return confirm('Supprimer cette inscription ?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-rose-600 hover:bg-rose-50 rounded-lg cursor-pointer transition" title="Supprimer">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-10 text-center text-slate-400 text-xs">
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

    {{-- ONGLET 2 : DÉTAILS DE L'ÉVÉNEMENT --}}
    <div x-show="activeTab === 'details'" style="display: none;" class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-7 space-y-6">
            {{-- Présentation --}}
            <div class="admin-card p-5 sm:p-6 space-y-3">
                <h3 class="text-sm font-semibold text-slate-900 flex items-center gap-2 border-b border-slate-100 pb-3">
                    <i data-lucide="file-text" class="w-4 h-4 text-red-700"></i>
                    <span>Présentation de l'Événement</span>
                </h3>
                <div class="prose prose-sm max-w-none text-slate-700">
                    {!! $event->description ?: '<p class="text-slate-400 text-xs">Aucune description détaillée.</p>' !!}
                </div>
            </div>

            {{-- Programme Horaire --}}
            <div class="admin-card p-5 sm:p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-semibold text-slate-900 flex items-center gap-2">
                        <i data-lucide="calendar-range" class="w-4 h-4 text-red-700"></i>
                        <span>Programme Officiel ({{ count($event->program ?? []) }} sessions)</span>
                    </h3>
                    <a href="{{ route('admin.events.edit', $event) }}" class="text-xs font-medium text-red-700 hover:underline">Modifier le programme</a>
                </div>

                <div class="space-y-3">
                    @forelse($event->program ?? [] as $slot)
                        <div class="p-3.5 rounded-lg border border-slate-200 bg-slate-50/70 space-y-1">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <span class="px-2 py-0.5 rounded bg-slate-900 text-white text-[11px] font-semibold">{{ $slot['time'] ?? '' }}</span>
                                <span class="text-xs text-slate-500 font-medium">{{ $slot['role'] ?? '' }} • {{ $slot['track'] ?? '' }}</span>
                            </div>
                            <div class="font-semibold text-slate-900 text-sm">{{ $slot['title'] ?? '' }}</div>
                            @if(!empty($slot['speaker']))
                                <div class="text-xs text-red-700 font-medium">Intervenant(s) : {{ $slot['speaker'] }}</div>
                            @endif
                            @if(!empty($slot['description']))
                                <p class="text-xs text-slate-600 mt-1">{{ $slot['description'] }}</p>
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
            <div class="admin-card p-5 sm:p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-semibold text-slate-900 flex items-center gap-2">
                        <i data-lucide="users" class="w-4 h-4 text-red-700"></i>
                        <span>Comité d'Organisation ({{ count($event->committee ?? []) }})</span>
                    </h3>
                </div>

                <div class="space-y-2.5">
                    @forelse($event->committee ?? [] as $member)
                        <div class="p-3 rounded-lg border border-slate-200 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                @if(!empty($member['photo_url']))
                                    <img src="{{ $member['photo_url'] }}" alt="{{ $member['name'] ?? '' }}" class="w-10 h-10 rounded-full object-cover border border-slate-200 shrink-0">
                                @else
                                    <div class="w-10 h-10 rounded-full bg-slate-900 text-white flex items-center justify-center font-bold text-xs shrink-0">
                                        CIO
                                    </div>
                                @endif
                                <div>
                                    <div class="font-semibold text-slate-900 text-xs">{{ $member['name'] ?? '' }}</div>
                                    <div class="text-xs text-red-700">{{ $member['role'] ?? '' }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $member['company'] ?? '' }}</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-1">
                                @if(!empty($member['linkedin']))
                                    <a href="{{ $member['linkedin'] }}" target="_blank" class="p-1 rounded text-slate-500 hover:text-blue-700">
                                        <i data-lucide="linkedin" class="w-3.5 h-3.5"></i>
                                    </a>
                                @endif
                                @if(!empty($member['twitter']))
                                    <a href="{{ $member['twitter'] }}" target="_blank" class="p-1 rounded text-slate-500 hover:text-slate-900">
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
            <div class="admin-card p-5 sm:p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-semibold text-slate-900 flex items-center gap-2">
                        <i data-lucide="handshake" class="w-4 h-4 text-red-700"></i>
                        <span>Partenaires & Sponsors ({{ count($event->partners ?? []) }})</span>
                    </h3>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    @forelse($event->partners ?? [] as $partner)
                        <div class="p-3 rounded-lg border border-slate-200 bg-slate-50 flex flex-col items-center text-center gap-1.5">
                            @if(!empty($partner['logo_url']))
                                <img src="{{ $partner['logo_url'] }}" alt="{{ $partner['name'] ?? '' }}" class="h-8 object-contain">
                            @endif
                            <div class="text-xs font-semibold text-slate-900">{{ $partner['name'] ?? '' }}</div>
                            <span class="admin-badge badge-warning text-[10px]">{{ $partner['tier'] ?? 'Partenaire' }}</span>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 col-span-2">Aucun partenaire renseigné.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- DIALOG MODAL : INSCRIRE UN PARTICIPANT --}}
    <div
        x-show="openAddParticipantModal"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/40 backdrop-blur-xs p-4"
        @keydown.escape.window="openAddParticipantModal = false"
    >
        <div @click.outside="openAddParticipantModal = false" class="bg-white rounded-xl border border-slate-200 shadow-xl max-w-lg w-full overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <h3 class="text-sm font-semibold text-slate-900 flex items-center gap-2">
                    <i data-lucide="user-plus" class="w-4 h-4 text-red-700"></i>
                    <span>Inscrire un participant à l'événement</span>
                </h3>
                <button type="button" @click="openAddParticipantModal = false" class="p-1 text-slate-400 hover:text-slate-600 rounded cursor-pointer">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form action="{{ route('admin.events.registrations.store', $event) }}" method="POST" class="p-5 space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="admin-label">Nom complet *</label>
                        <input type="text" name="full_name" required placeholder="Ex: Dr. Seydou Koné" class="admin-input">
                    </div>
                    <div>
                        <label class="admin-label">Email professionnel *</label>
                        <input type="email" name="email" required placeholder="s.kone@entreprise.com" class="admin-input">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="admin-label">Entreprise / Institution</label>
                        <input type="text" name="company" placeholder="Ex: Groupe Banque Atlantique" class="admin-input">
                    </div>
                    <div>
                        <label class="admin-label">Fonction / Poste</label>
                        <input type="text" name="job_title" placeholder="Ex: DSI Groupe" class="admin-input">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="admin-label">Téléphone</label>
                        <input type="text" name="phone" placeholder="+225 07 00 00 00" class="admin-input">
                    </div>
                    <div>
                        <label class="admin-label">Pays</label>
                        <input type="text" name="country" placeholder="Côte d'Ivoire" class="admin-input">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="admin-label">Mode de participation</label>
                        <x-ui.select
                            name="attendance_mode"
                            value="in_person"
                            :options="[
                                ['value' => 'in_person', 'label' => 'Présentiel (Badge VIP)'],
                                ['value' => 'online', 'label' => 'Distanciel (Streaming)'],
                            ]"
                        />
                    </div>
                    <div>
                        <label class="admin-label">Statut initial</label>
                        <x-ui.select
                            name="status"
                            value="confirmed"
                            :options="[
                                ['value' => 'confirmed', 'label' => 'Confirmé immédiatement'],
                                ['value' => 'pending', 'label' => 'En attente de validation'],
                            ]"
                        />
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="openAddParticipantModal = false" class="btn btn-sm btn-ghost">
                        Annuler
                    </button>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Générer l'accréditation</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
