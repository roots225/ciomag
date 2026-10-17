@extends('layouts.admin')

@section('title', 'Événements & Accréditations')
@section('header_title', 'Événements, Sommets & Inscriptions')

@section('top_actions')
    <div class="flex items-center gap-2">
        <a href="{{ route('magazine.events.index') }}" target="_blank" class="btn btn-sm btn-secondary">
            <i data-lucide="external-link" class="w-4 h-4 text-slate-500"></i>
            <span>Agenda public</span>
        </a>
        <a href="{{ route('admin.events.create') }}" class="btn btn-sm btn-primary">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Nouvel événement</span>
        </a>
    </div>
@endsection

@section('content')
<div class="space-y-6">

    {{-- Statistiques rapides --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="admin-card p-4 flex items-center justify-between">
            <div>
                <span class="text-xs font-medium text-slate-500">Total Événements</span>
                <div class="text-xl font-bold text-slate-900 mt-0.5">{{ $events->total() }}</div>
                <span class="text-[11px] text-slate-400">Programmés</span>
            </div>
            <div class="w-10 h-10 rounded-lg bg-red-50 text-red-700 flex items-center justify-center">
                <i data-lucide="calendar-days" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="admin-card p-4 flex items-center justify-between">
            <div>
                <span class="text-xs font-medium text-slate-500">À venir</span>
                <div class="text-xl font-bold text-slate-900 mt-0.5">{{ \App\Models\Event::upcoming()->count() }}</div>
                <span class="text-[11px] text-emerald-600 font-medium">Décompte actif</span>
            </div>
            <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <i data-lucide="timer" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="admin-card p-4 flex items-center justify-between">
            <div>
                <span class="text-xs font-medium text-slate-500">Inscriptions</span>
                <div class="text-xl font-bold text-slate-900 mt-0.5">{{ $registrations->total() }}</div>
                <span class="text-[11px] text-slate-400">Total enregistrées</span>
            </div>
            <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                <i data-lucide="ticket" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="admin-card p-4 flex items-center justify-between">
            <div>
                <span class="text-xs font-medium text-slate-500">Accréditations confirmées</span>
                <div class="text-xl font-bold text-slate-900 mt-0.5">{{ \App\Models\EventRegistration::where('status', 'confirmed')->count() }}</div>
                <span class="text-[11px] text-emerald-600 font-medium">Badges validés</span>
            </div>
            <div class="w-10 h-10 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                <i data-lucide="users" class="w-5 h-5"></i>
            </div>
        </div>
    </div>

    {{-- Liste des événements --}}
    <div class="admin-table-container">
        <div class="px-5 py-3.5 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-sm font-semibold text-slate-900 flex items-center gap-2">
                    <i data-lucide="calendar-clock" class="w-4 h-4 text-red-700"></i>
                    <span>Sommets, Forums & Tables Rondes CIOMAG ({{ $events->total() }})</span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Compte à rebours, programme horaire, comité d'organisation et partenaires.</p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('magazine.events.index') }}" target="_blank" class="btn btn-sm btn-secondary">
                    <i data-lucide="external-link" class="w-3.5 h-3.5 text-slate-500"></i>
                    <span>Agenda public</span>
                </a>
                <a href="{{ route('admin.events.create') }}" class="btn btn-sm btn-primary">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Ajouter un événement</span>
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Événement</th>
                        <th>Date & Compte à rebours</th>
                        <th>Lieu / Format</th>
                        <th>Contenu</th>
                        <th>Inscrits</th>
                        <th>Statut</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($events as $event)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td>
                                <div class="flex items-center gap-3">
                                    @if($event->cover_url)
                                        <img src="{{ $event->cover_url }}" alt="{{ $event->title }}" class="w-12 h-9 rounded-md object-cover border border-slate-200 shrink-0">
                                    @else
                                        <div class="w-12 h-9 rounded-md bg-slate-900 text-white flex items-center justify-center shrink-0">
                                            <i data-lucide="calendar" class="w-4 h-4 text-red-500"></i>
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <div class="font-semibold text-slate-900 flex items-center gap-1.5 truncate">
                                            <a href="{{ route('admin.events.show', $event) }}" class="hover:text-red-700 transition">
                                                {{ $event->title }}
                                            </a>
                                            @if($event->is_featured)
                                                <span class="admin-badge badge-warning text-[9px] py-0 px-1.5">À la Une</span>
                                            @endif
                                        </div>
                                        <div class="text-[11px] text-slate-500">{{ $event->event_type }} • {{ $event->formattedPrice() }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-xs">
                                <div class="font-medium text-slate-800">
                                    {{ $event->starts_at?->translatedFormat('d M Y • H:i') }}
                                </div>
                                @if($event->starts_at && $event->starts_at->isFuture())
                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-700 mt-0.5">
                                        <i data-lucide="timer" class="w-3 h-3"></i>
                                        J-{{ (int) now()->diffInDays($event->starts_at) }}
                                    </span>
                                @else
                                    <span class="text-[11px] text-slate-400">Passé</span>
                                @endif
                            </td>
                            <td class="text-xs text-slate-600">
                                <div class="font-medium text-slate-800">{{ $event->city ?: 'En ligne' }}{{ $event->country ? ', ' . $event->country : '' }}</div>
                                <div class="text-[11px] text-slate-400 truncate max-w-[160px]">{{ $event->venue ?: ($event->is_online ? 'Streaming Live' : '—') }}</div>
                            </td>
                            <td>
                                <div class="flex flex-wrap items-center gap-1.5 text-[11px]">
                                    <span class="admin-badge badge-neutral">
                                        {{ count($event->program ?? []) }} sessions
                                    </span>
                                    <span class="admin-badge badge-neutral">
                                        {{ count($event->committee ?? []) }} membres
                                    </span>
                                    <span class="admin-badge badge-neutral">
                                        {{ count($event->partners ?? []) }} partenaires
                                    </span>
                                </div>
                            </td>
                            <td>
                                <a href="{{ route('admin.events.show', $event) }}" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-slate-100 hover:bg-red-50 hover:text-red-700 text-xs transition">
                                    <span class="font-bold text-slate-900">{{ $event->registrations_count }}</span>
                                    <span class="text-slate-400">/ {{ $event->capacity }}</span>
                                </a>
                            </td>
                            <td>
                                @if($event->status === 'published')
                                    <span class="admin-badge badge-success">Publié</span>
                                @elseif($event->status === 'draft')
                                    <span class="admin-badge badge-warning">Brouillon</span>
                                @else
                                    <span class="admin-badge badge-neutral">{{ ucfirst($event->status) }}</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="inline-flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.events.show', $event) }}" class="btn btn-sm btn-secondary">
                                        <i data-lucide="users" class="w-3.5 h-3.5"></i>
                                        <span>Détails</span>
                                    </a>
                                    <a href="{{ route('admin.events.edit', $event) }}" class="p-1.5 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition" title="Modifier">
                                        <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                    </a>
                                    <a href="{{ route('magazine.events.show', $event->slug) }}" target="_blank" class="p-1.5 rounded-lg text-slate-400 hover:text-red-700 transition" title="Voir la page publique">
                                        <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                    </a>
                                    <form action="{{ route('admin.events.destroy', $event) }}" method="POST" onsubmit="return confirm('Supprimer cet événement ?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-lg text-rose-600 hover:bg-rose-50 transition cursor-pointer" title="Supprimer">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-slate-400 text-xs">
                                Aucun événement programmé.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $events->links() }}
        </div>
    </div>

    {{-- Liste des inscriptions aux événements --}}
    <div class="admin-table-container">
        <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-semibold text-slate-900 flex items-center gap-2">
                    <i data-lucide="badge-check" class="w-4 h-4 text-red-700"></i>
                    <span>Inscriptions & Accréditations Participants ({{ $registrations->total() }})</span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Suivi en temps réel des badges et inscriptions enregistrés.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Code Badge</th>
                        <th>Participant</th>
                        <th>Organisation</th>
                        <th>Événement</th>
                        <th>Mode</th>
                        <th>Statut</th>
                        <th class="text-right">Validation</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($registrations as $reg)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="font-mono text-xs font-semibold text-red-700">{{ $reg->ticket_code }}</td>
                            <td class="text-xs">
                                <div class="font-semibold text-slate-900">{{ $reg->full_name }}</div>
                                <div class="text-slate-500">{{ $reg->email }} {{ $reg->phone ? '• ' . $reg->phone : '' }}</div>
                            </td>
                            <td class="text-xs">
                                <div class="font-medium text-slate-800">{{ $reg->company ?: '—' }}</div>
                                <div class="text-slate-400">{{ $reg->job_title ?: 'Participant' }}</div>
                            </td>
                            <td class="text-xs font-medium text-slate-800">
                                {{ $reg->event?->title ?? 'Événement supprimé' }}
                            </td>
                            <td class="text-xs">
                                <span class="admin-badge {{ $reg->attendance_mode === 'online' ? 'badge-info' : 'badge-neutral' }}">
                                    {{ $reg->attendance_mode === 'online' ? 'Distanciel' : 'Présentiel' }}
                                </span>
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
                                <form action="{{ route('admin.events.registrations.status', $reg) }}" method="POST" class="inline-flex items-center justify-end gap-1.5">
                                    @csrf
                                    @method('PATCH')
                                    <div class="w-32">
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
                                    <button type="submit" class="btn btn-sm btn-dark" title="Mettre à jour">
                                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400 text-xs">Aucune inscription enregistrée.</td>
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
@endsection
