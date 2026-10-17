@extends('layouts.admin')

@section('title', 'Événements & Accréditations')
@section('header_title', 'Événements, Sommets & Inscriptions')

@section('top_actions')
    <a href="{{ route('magazine.events.index') }}" target="_blank" class="px-3.5 py-2 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition flex items-center gap-1.5">
        <i data-lucide="external-link" class="w-3.5 h-3.5 text-red-700"></i>
        <span>Voir l'agenda public</span>
    </a>
    <a href="{{ route('admin.events.create') }}" class="px-4 py-2 rounded-lg bg-red-700 hover:bg-red-800 text-white text-xs font-bold transition shadow-xs flex items-center gap-1.5">
        <i data-lucide="calendar-plus" class="w-4 h-4"></i>
        <span>Nouvel événement</span>
    </a>
@endsection

@section('content')
<div class="space-y-8">

    {{-- Statistiques rapides --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-slate-200 p-4 flex items-center gap-3.5 shadow-2xs">
            <div class="w-11 h-11 rounded-xl bg-red-50 text-red-700 flex items-center justify-center shrink-0">
                <i data-lucide="calendar-days" class="w-5 h-5"></i>
            </div>
            <div>
                <div class="text-xs text-slate-500 font-medium">Total Événements</div>
                <div class="text-xl font-black text-slate-900">{{ $events->total() }}</div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 flex items-center gap-3.5 shadow-2xs">
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0">
                <i data-lucide="timer" class="w-5 h-5"></i>
            </div>
            <div>
                <div class="text-xs text-slate-500 font-medium">À venir (Compte à rebours actif)</div>
                <div class="text-xl font-black text-slate-900">{{ \App\Models\Event::upcoming()->count() }}</div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 flex items-center gap-3.5 shadow-2xs">
            <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center shrink-0">
                <i data-lucide="ticket" class="w-5 h-5"></i>
            </div>
            <div>
                <div class="text-xs text-slate-500 font-medium">Inscriptions enregistrées</div>
                <div class="text-xl font-black text-slate-900">{{ $registrations->total() }}</div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 flex items-center gap-3.5 shadow-2xs">
            <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-700 flex items-center justify-center shrink-0">
                <i data-lucide="users" class="w-5 h-5"></i>
            </div>
            <div>
                <div class="text-xs text-slate-500 font-medium">Accréditations confirmées</div>
                <div class="text-xl font-black text-slate-900">{{ \App\Models\EventRegistration::where('status', 'confirmed')->count() }}</div>
            </div>
        </div>
    </div>

    {{-- Liste des événements --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="calendar-clock" class="w-4 h-4 text-red-700"></i>
                    <span>Sommets, Forums & Tables Rondes CIOMAG</span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Gérez le compte à rebours, le programme horaire, le comité d'organisation et les logos partenaires.</p>
            </div>

            <div class="flex items-center gap-2.5">
                <a href="{{ route('magazine.events.index') }}" target="_blank" class="px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition inline-flex items-center gap-1.5">
                    <i data-lucide="external-link" class="w-3.5 h-3.5 text-red-700"></i>
                    <span>Voir l'agenda public</span>
                </a>
                <a href="{{ route('admin.events.create') }}" class="px-4 py-2.5 rounded-xl bg-red-700 hover:bg-red-800 text-white text-xs font-bold uppercase tracking-wider transition shadow-sm inline-flex items-center gap-2">
                    <i data-lucide="calendar-plus" class="w-4 h-4"></i>
                    <span>Ajouter un événement</span>
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="py-3.5 px-4">Événement</th>
                        <th class="py-3.5 px-4">Date & Compte à rebours</th>
                        <th class="py-3.5 px-4">Lieu / Format</th>
                        <th class="py-3.5 px-4">Contenu (Prog / Comité / Part.)</th>
                        <th class="py-3.5 px-4">Inscrits</th>
                        <th class="py-3.5 px-4">Statut</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($events as $event)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    @if($event->cover_url)
                                        <img src="{{ $event->cover_url }}" alt="{{ $event->title }}" class="w-14 h-10 rounded-lg object-cover border border-slate-200 shrink-0">
                                    @else
                                        <div class="w-14 h-10 rounded-lg bg-slate-900 text-white flex items-center justify-center shrink-0">
                                            <i data-lucide="calendar" class="w-4 h-4 text-red-500"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <div class="font-bold text-slate-900 flex items-center gap-1.5">
                                            <a href="{{ route('admin.events.show', $event) }}" class="hover:text-red-700 transition">
                                                {{ $event->title }}
                                            </a>
                                            @if($event->is_featured)
                                                <span class="px-1.5 py-0.5 text-[10px] font-extrabold uppercase bg-amber-100 text-amber-800 rounded">À la Une</span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-slate-500">{{ $event->event_type }} • {{ $event->formattedPrice() }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-slate-800 text-xs">
                                    {{ $event->starts_at?->translatedFormat('d M Y • H:i') }}
                                </div>
                                @if($event->starts_at && $event->starts_at->isFuture())
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 mt-0.5">
                                        <i data-lucide="timer" class="w-3 h-3"></i>
                                        J-{{ (int) now()->diffInDays($event->starts_at) }}
                                    </span>
                                @else
                                    <span class="text-[11px] text-slate-400">Passé</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-xs text-slate-600">
                                <div class="font-semibold text-slate-800">{{ $event->city ?: 'En ligne' }}{{ $event->country ? ', ' . $event->country : '' }}</div>
                                <div class="text-[11px] text-slate-400 truncate max-w-[180px]">{{ $event->venue ?: ($event->is_online ? 'Streaming Live' : '—') }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex flex-wrap items-center gap-1.5 text-[11px] font-semibold">
                                    <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 inline-flex items-center gap-1">
                                        <i data-lucide="list-checks" class="w-3 h-3 text-red-700"></i>
                                        {{ count($event->program ?? []) }} sess.
                                    </span>
                                    <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 inline-flex items-center gap-1">
                                        <i data-lucide="users" class="w-3 h-3 text-indigo-700"></i>
                                        {{ count($event->committee ?? []) }} membres
                                    </span>
                                    <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 inline-flex items-center gap-1">
                                        <i data-lucide="award" class="w-3 h-3 text-amber-600"></i>
                                        {{ count($event->partners ?? []) }} logos
                                    </span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <a href="{{ route('admin.events.show', $event) }}" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-red-50 hover:text-red-700 transition">
                                    <i data-lucide="users" class="w-3.5 h-3.5 text-red-700"></i>
                                    <span class="font-bold text-slate-900">{{ $event->registrations_count }}</span>
                                    <span class="text-xs text-slate-400">/ {{ $event->capacity }}</span>
                                </a>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($event->status === 'published')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Publié
                                    </span>
                                @elseif($event->status === 'draft')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        Brouillon
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600">
                                        {{ ucfirst($event->status) }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('admin.events.show', $event) }}" class="px-2.5 py-1.5 rounded-lg bg-slate-900 hover:bg-red-700 text-white text-xs font-bold transition inline-flex items-center gap-1" title="Détails & Participants inscrits">
                                        <i data-lucide="users" class="w-3.5 h-3.5"></i>
                                        <span>Détails & Inscrits</span>
                                    </a>
                                    <a href="{{ route('admin.events.edit', $event) }}" class="p-1.5 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition" title="Modifier l'événement">
                                        <i data-lucide="pen-square" class="w-4 h-4"></i>
                                    </a>
                                    <a href="{{ route('magazine.events.show', $event->slug) }}" target="_blank" class="p-1.5 rounded-lg text-slate-500 hover:text-red-700 hover:bg-red-50 transition" title="Voir la page publique">
                                        <i data-lucide="external-link" class="w-4 h-4"></i>
                                    </a>
                                    <form action="{{ route('admin.events.destroy', $event) }}" method="POST" onsubmit="return confirm('Supprimer cet événement ?')" class="inline">
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
                            <td colspan="7" class="py-10 text-center text-slate-400">
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
    <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="badge-check" class="w-4 h-4 text-red-700"></i>
                    <span>Inscriptions & Accréditations Participants</span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Suivi en temps réel des badges et inscriptions enregistrés sur les pages d'événements.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="py-3 px-4">Code Badge</th>
                        <th class="py-3 px-4">Participant</th>
                        <th class="py-3 px-4">Organisation & Fonction</th>
                        <th class="py-3 px-4">Événement</th>
                        <th class="py-3 px-4">Mode</th>
                        <th class="py-3 px-4">Statut</th>
                        <th class="py-3 px-4 text-right">Validation</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($registrations as $reg)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3 px-4 font-mono text-xs font-bold text-red-700">{{ $reg->ticket_code }}</td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-900">{{ $reg->full_name }}</div>
                                <div class="text-xs text-slate-500">{{ $reg->email }} {{ $reg->phone ? '• ' . $reg->phone : '' }}</div>
                            </td>
                            <td class="py-3 px-4 text-xs">
                                <div class="font-semibold text-slate-800">{{ $reg->company ?: '—' }}</div>
                                <div class="text-slate-500">{{ $reg->job_title ?: 'Participant' }}</div>
                            </td>
                            <td class="py-3 px-4 text-xs font-semibold text-slate-800">
                                {{ $reg->event?->title ?? 'Événement supprimé' }}
                            </td>
                            <td class="py-3 px-4 text-xs">
                                @if($reg->attendance_mode === 'online')
                                    <span class="px-2 py-0.5 rounded bg-sky-50 text-sky-700 font-semibold">Distanciel</span>
                                @else
                                    <span class="px-2 py-0.5 rounded bg-purple-50 text-purple-700 font-semibold">Présentiel</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                @if($reg->status === 'confirmed')
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700">Confirmé</span>
                                @elseif($reg->status === 'pending')
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700">En attente</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700">Annulé</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right">
                                <form action="{{ route('admin.events.registrations.status', $reg) }}" method="POST" class="inline-flex items-center gap-1.5">
                                    @csrf
                                    @method('PATCH')
                                    <div class="w-36">
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
                                    <button type="submit" class="p-2 rounded-lg bg-slate-900 hover:bg-red-700 text-white transition cursor-pointer" title="Mettre à jour">
                                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">Aucune inscription enregistrée.</td>
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
