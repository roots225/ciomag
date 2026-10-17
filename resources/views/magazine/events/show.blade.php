@extends('layouts.magazine')

@section('meta_title', $event->title . ' — Événements CIOMAG')

@section('content')
@php
    $attendanceOptions = [
        'in_person' => 'Participation en Présentiel (Badge VIP)',
        'online' => 'Participation à Distance (Streaming Live HD)',
    ];
@endphp

<div
    class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-14"
    x-data="eventDetailCountdown('{{ $event->starts_at->toIso8601String() }}')"
    x-init="start()"
>

    <!-- Hero de Présentation de l'Événement + Décompte Jusqu'au Jour J -->
    <section class="bg-slate-950 text-white rounded-3xl overflow-hidden shadow-xl border border-slate-800 relative">
        @if($event->cover_url)
            <img src="{{ $event->cover_url }}" alt="{{ $event->title }}" class="absolute inset-0 w-full h-full object-cover opacity-30">
        @endif
        <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/85 to-slate-950/60"></div>

        <div class="relative z-10 p-8 sm:p-12 grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
            <div class="lg:col-span-7 space-y-4">
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('magazine.events.index') }}" class="px-3 py-1 rounded-full bg-white/10 hover:bg-white/20 text-white text-xs font-bold inline-flex items-center gap-1">
                        <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                        <span>Tous les événements</span>
                    </a>
                    <span class="px-3 py-1 rounded-full bg-ciomag-pine text-white text-xs font-black uppercase tracking-wider border border-white/10">
                        {{ $event->event_type }}
                    </span>
                    <span class="px-3 py-1 rounded-full bg-ciomag-amber text-slate-950 text-xs font-black uppercase">
                        {{ $event->formattedPrice() }}
                    </span>
                </div>

                <h1 class="text-3xl sm:text-5xl font-black leading-tight tracking-tight">
                    {{ $event->title }}
                </h1>

                @if($event->subtitle)
                    <p class="text-lg text-ciomag-amber font-semibold">{{ $event->subtitle }}</p>
                @endif

                <div class="pt-2 grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs text-slate-200">
                    <div class="flex items-center gap-2.5 bg-white/5 p-3 rounded-xl border border-white/10">
                        <i data-lucide="calendar" class="w-4 h-4 text-ciomag-amber shrink-0"></i>
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Date & Horaires</span>
                            <span class="font-bold">{{ $event->starts_at->translatedFormat('d F Y • H:i') }}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5 bg-white/5 p-3 rounded-xl border border-white/10">
                        <i data-lucide="map-pin" class="w-4 h-4 text-ciomag-amber shrink-0"></i>
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Lieu de l'événement</span>
                            <span class="font-bold">{{ $event->locationLabel() }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Compte à rebours jusqu'au jour de l'événement -->
            <div id="event-countdown" class="lg:col-span-5 bg-black/40 border border-ciomag-amber/40 rounded-2xl p-6 sm:p-8 text-center space-y-5 shadow-2xl backdrop-blur-md">
                <div class="flex items-center justify-center gap-2 text-ciomag-amber text-xs font-extrabold uppercase tracking-widest">
                    <i data-lucide="timer" class="w-4 h-4"></i>
                    <span>Décompte jusqu'au jour de l'événement</span>
                </div>

                <div class="grid grid-cols-4 gap-3">
                    <div class="bg-black/50 border border-white/10 rounded-xl p-3.5">
                        <span class="text-2xl sm:text-4xl font-black text-white block font-mono" x-text="days">00</span>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Jours</span>
                    </div>
                    <div class="bg-black/50 border border-white/10 rounded-xl p-3.5">
                        <span class="text-2xl sm:text-4xl font-black text-white block font-mono" x-text="hours">00</span>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Heures</span>
                    </div>
                    <div class="bg-black/50 border border-white/10 rounded-xl p-3.5">
                        <span class="text-2xl sm:text-4xl font-black text-white block font-mono" x-text="minutes">00</span>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Minutes</span>
                    </div>
                    <div class="bg-black/50 border border-white/10 rounded-xl p-3.5">
                        <span class="text-2xl sm:text-4xl font-black text-ciomag-amber block font-mono" x-text="seconds">00</span>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Secondes</span>
                    </div>
                </div>

                <a href="#event-registration" class="w-full py-3.5 bg-ciomag-amber hover:bg-ciomag-amber-hover text-slate-950 text-xs font-black uppercase tracking-wider rounded-xl inline-flex items-center justify-center gap-2 shadow-lg transition">
                    <i data-lucide="user-plus" class="w-4 h-4"></i>
                    <span>Réserver mon accréditation maintenant</span>
                </a>
            </div>
        </div>
    </section>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
        <!-- Colonne Principale : Présentation, Programme, Comité, Partenaires (8 colonnes) -->
        <div class="lg:col-span-8 space-y-12">

            <!-- 1. Présentation de l'événement -->
            <section class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-2xs space-y-4">
                <h2 class="text-lg font-black uppercase tracking-wider text-slate-950 border-b border-slate-100 pb-3 flex items-center gap-2">
                    <i data-lucide="info" class="w-5 h-5 text-ciomag-teal"></i>
                    <span>À propos de l'événement</span>
                </h2>
                @if($event->excerpt)
                    <p class="text-base font-semibold text-slate-700 leading-relaxed">{{ $event->excerpt }}</p>
                @endif
                <div class="article-content text-sm text-slate-600">
                    {!! $event->description !!}
                </div>
            </section>

            <!-- 2. Programme Officiel de l'Événement -->
            @if(!empty($event->program))
                <section id="event-program" class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-2xs space-y-6">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h2 class="text-lg font-black uppercase tracking-wider text-slate-950 flex items-center gap-2">
                            <i data-lucide="list-ordered" class="w-5 h-5 text-ciomag-teal"></i>
                            <span>Programme Officiel des Sessions</span>
                        </h2>
                        <span class="text-xs font-bold text-slate-500">{{ count($event->program) }} temps forts</span>
                    </div>

                    <div class="space-y-4">
                        @foreach($event->program as $session)
                            <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80 flex flex-col sm:flex-row items-start gap-4 hover:border-ciomag-teal/40 transition">
                                <div class="px-3.5 py-2 rounded-xl bg-[#182322] text-ciomag-amber text-xs font-extrabold font-mono shrink-0">
                                    {{ $session['time'] ?? 'Horaire' }}
                                </div>
                                <div class="space-y-1.5 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        @if(!empty($session['role']))
                                            <span class="px-2 py-0.5 text-[10px] font-bold uppercase bg-ciomag-teal-pale text-ciomag-pine rounded-xs">
                                                {{ $session['role'] }}
                                            </span>
                                        @endif
                                        @if(!empty($session['track']))
                                            <span class="text-xs text-slate-400 inline-flex items-center gap-1">
                                                <i data-lucide="map-pin" class="w-3 h-3"></i>
                                                <span>{{ $session['track'] }}</span>
                                            </span>
                                        @endif
                                    </div>
                                    <h3 class="text-base font-extrabold text-slate-900">{{ $session['title'] ?? '' }}</h3>
                                    @if(!empty($session['speaker']))
                                        <p class="text-xs font-bold text-ciomag-teal flex items-center gap-1.5">
                                            <i data-lucide="mic" class="w-3.5 h-3.5"></i>
                                            <span>{{ $session['speaker'] }}</span>
                                        </p>
                                    @endif
                                    @if(!empty($session['description']))
                                        <p class="text-xs text-slate-600 leading-relaxed">{{ $session['description'] }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            <!-- 3. Membres du Comité d'Organisation & Speakers (Photos + Profils Réseaux Sociaux) -->
            @if(!empty($event->committee))
                <section id="event-committee" class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-2xs space-y-6">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h2 class="text-lg font-black uppercase tracking-wider text-slate-950 flex items-center gap-2">
                            <i data-lucide="users" class="w-5 h-5 text-ciomag-teal"></i>
                            <span>Comité d'Organisation & Intervenants</span>
                        </h2>
                        <span class="text-xs font-bold text-slate-500">{{ count($event->committee) }} membres</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        @foreach($event->committee as $member)
                            <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50/60 flex items-start gap-4">
                                @if(!empty($member['photo_url']))
                                    <img
                                        src="{{ $member['photo_url'] }}"
                                        alt="{{ $member['name'] ?? 'Membre' }}"
                                        class="w-16 h-16 rounded-2xl object-cover border-2 border-ciomag-teal shrink-0"
                                    >
                                @else
                                    <div class="w-16 h-16 rounded-2xl bg-ciomag-pine text-white flex items-center justify-center font-black text-lg shrink-0">
                                        {{ mb_substr($member['name'] ?? 'C', 0, 1) }}
                                    </div>
                                @endif

                                <div class="space-y-1.5 min-w-0 flex-1">
                                    <h3 class="text-base font-extrabold text-slate-950 truncate">{{ $member['name'] ?? '' }}</h3>
                                    @if(!empty($member['role']))
                                        <p class="text-xs font-bold text-ciomag-teal">{{ $member['role'] }}</p>
                                    @endif
                                    @if(!empty($member['company']))
                                        <p class="text-xs text-slate-500">{{ $member['company'] }}</p>
                                    @endif

                                    <!-- Liens vers les profils Réseaux Sociaux -->
                                    <div class="pt-2 flex items-center gap-2">
                                        @if(!empty($member['linkedin']))
                                            <a
                                                href="{{ $member['linkedin'] }}"
                                                target="_blank"
                                                rel="noopener"
                                                class="px-2.5 py-1 rounded-lg bg-slate-900 hover:bg-ciomag-teal text-white text-[11px] font-bold inline-flex items-center gap-1 transition"
                                                title="Profil LinkedIn"
                                            >
                                                <i data-lucide="linkedin" class="w-3 h-3"></i>
                                                <span>LinkedIn</span>
                                            </a>
                                        @endif
                                        @if(!empty($member['twitter']))
                                            <a
                                                href="{{ $member['twitter'] }}"
                                                target="_blank"
                                                rel="noopener"
                                                class="px-2.5 py-1 rounded-lg bg-slate-200 hover:bg-slate-900 hover:text-white text-slate-800 text-[11px] font-bold inline-flex items-center gap-1 transition"
                                                title="Profil X / Twitter"
                                            >
                                                <i data-lucide="twitter" class="w-3 h-3"></i>
                                                <span>X / Twitter</span>
                                            </a>
                                        @endif
                                        @if(!empty($member['website']))
                                            <a
                                                href="{{ $member['website'] }}"
                                                target="_blank"
                                                rel="noopener"
                                                class="p-1.5 rounded-lg bg-slate-200 hover:bg-slate-300 text-slate-700 transition"
                                                title="Site Web"
                                            >
                                                <i data-lucide="globe" class="w-3.5 h-3.5"></i>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            <!-- 4. Logos des Partenaires & Sponsors Officiels -->
            @if(!empty($event->partners))
                <section id="event-partners" class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-2xs space-y-6">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h2 class="text-lg font-black uppercase tracking-wider text-slate-950 flex items-center gap-2">
                            <i data-lucide="handshake" class="w-5 h-5 text-ciomag-teal"></i>
                            <span>Partenaires & Sponsors Officiels</span>
                        </h2>
                        <span class="text-xs font-bold text-slate-500">{{ count($event->partners) }} partenaires</span>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        @foreach($event->partners as $partner)
                            <a
                                href="{{ !empty($partner['website']) ? $partner['website'] : '#' }}"
                                target="_blank"
                                rel="noopener"
                                class="p-4 rounded-2xl border border-slate-200 hover:border-ciomag-teal bg-slate-50/50 flex flex-col items-center justify-between text-center gap-3 group transition"
                            >
                                @if(!empty($partner['logo_url']))
                                    <img
                                        src="{{ $partner['logo_url'] }}"
                                        alt="{{ $partner['name'] ?? 'Partenaire' }}"
                                        class="h-14 w-auto object-contain rounded-lg group-hover:scale-105 transition"
                                    >
                                @else
                                    <div class="h-14 flex items-center justify-center text-slate-400">
                                        <i data-lucide="building-2" class="w-8 h-8"></i>
                                    </div>
                                @endif
                                <div>
                                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-ciomag-teal block">
                                        {{ $partner['tier'] ?? 'Partenaire' }}
                                    </span>
                                    <span class="text-xs font-bold text-slate-900 block mt-0.5">
                                        {{ $partner['name'] ?? '' }}
                                    </span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>

        <!-- Colonne Latérale : Formulaire d'Inscription à l'Événement (4 colonnes) -->
        <div class="lg:col-span-4">
            <div id="event-registration" class="bg-white rounded-2xl border-2 border-ciomag-teal p-6 sm:p-8 shadow-xl space-y-5 sticky top-24">
                <div class="border-b border-slate-100 pb-4">
                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-ciomag-teal">Accréditation & Billetterie</span>
                    <h2 class="text-xl font-black text-slate-950 mt-0.5">S'inscrire à l'événement</h2>
                    <p class="text-xs text-slate-500 mt-1">
                        {{ $event->confirmed_registrations_count }} participants déjà inscrits sur {{ $event->capacity }} places.
                    </p>
                </div>

                @if($event->registration_open)
                    <form action="{{ route('magazine.events.register', $event->slug) }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nom & Prénom *</label>
                            <input type="text" name="full_name" value="{{ old('full_name', auth()->user()?->display_name) }}" required placeholder="Ex: Fatou Ndiaye" class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:outline-none focus:border-ciomag-teal">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Email Professionnel *</label>
                            <input type="email" name="email" value="{{ old('email', auth()->user()?->email) }}" required placeholder="nom@organisation.com" class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:outline-none focus:border-ciomag-teal">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Entreprise / Ministère *</label>
                                <input type="text" name="company" value="{{ old('company') }}" required placeholder="Organisation" class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:outline-none focus:border-ciomag-teal">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Fonction *</label>
                                <input type="text" name="job_title" value="{{ old('job_title') }}" required placeholder="DSI, RSSI, DG..." class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:outline-none focus:border-ciomag-teal">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Téléphone</label>
                                <input type="text" name="phone" value="{{ old('phone') }}" placeholder="+225 / +212..." class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:outline-none focus:border-ciomag-teal">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Pays</label>
                                <input type="text" name="country" value="{{ old('country', $event->country) }}" class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:outline-none focus:border-ciomag-teal">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Mode de participation *</label>
                            <x-ui.select
                                name="attendance_mode"
                                :options="$attendanceOptions"
                                value="in_person"
                                :searchable="false"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Ateliers ou attentes particulières</label>
                            <textarea name="notes" rows="2" class="w-full px-3.5 py-2 text-sm border border-slate-300 rounded-xl focus:outline-none focus:border-ciomag-teal">{{ old('notes') }}</textarea>
                        </div>

                        <button type="submit" class="w-full py-3.5 bg-ciomag-amber hover:bg-ciomag-amber-hover text-slate-950 text-sm font-black uppercase tracking-wider rounded-xl inline-flex items-center justify-center gap-2 shadow-md transition cursor-pointer">
                            <i data-lucide="ticket-check" class="w-4 h-4"></i>
                            <span>Confirmer mon inscription</span>
                        </button>
                    </form>
                @else
                    <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-800 font-semibold">
                        Les inscriptions en ligne sont actuellement closes pour cet événement.
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>

<script>
    function eventDetailCountdown(targetIso) {
        return {
            days: '00',
            hours: '00',
            minutes: '00',
            seconds: '00',
            timer: null,
            start() {
                this.update();
                this.timer = setInterval(() => this.update(), 1000);
            },
            update() {
                const target = new Date(targetIso).getTime();
                const now = Date.now();
                const diff = Math.max(0, target - now);
                const d = Math.floor(diff / (1000 * 60 * 60 * 24));
                const h = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const m = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                const s = Math.floor((diff % (1000 * 60)) / 1000);
                this.days = String(d).padStart(2, '0');
                this.hours = String(h).padStart(2, '0');
                this.minutes = String(m).padStart(2, '0');
                this.seconds = String(s).padStart(2, '0');
            }
        };
    }
</script>
@endsection
