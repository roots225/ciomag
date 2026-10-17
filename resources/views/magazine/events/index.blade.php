@extends('layouts.magazine')

@section('meta_title', 'Événements, Assises & Forums des DSI — CIOMAG')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-12">

    <!-- Événement Phare avec Compte à Rebours -->
    @if($featuredEvent)
        <section
            x-data="eventCountdown('{{ $featuredEvent->starts_at->toIso8601String() }}')"
            x-init="start()"
            class="bg-slate-950 text-white rounded-3xl overflow-hidden shadow-xl border border-slate-800 relative"
        >
            @if($featuredEvent->cover_url)
                <img src="{{ $featuredEvent->cover_url }}" alt="{{ $featuredEvent->title }}" class="absolute inset-0 w-full h-full object-cover opacity-35">
            @endif
            <div class="absolute inset-0 bg-gradient-to-r from-slate-950 via-slate-950/90 to-slate-950/60"></div>

            <div class="relative z-10 p-8 sm:p-12 grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                <div class="lg:col-span-7 space-y-4">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="px-3 py-1 rounded-full bg-red-700 text-white text-xs font-extrabold uppercase tracking-wider inline-flex items-center gap-1.5">
                            <i data-lucide="calendar-clock" class="w-3.5 h-3.5"></i>
                            <span>{{ $featuredEvent->event_type }}</span>
                        </span>
                        <span class="px-3 py-1 rounded-full bg-white/10 text-amber-300 text-xs font-bold inline-flex items-center gap-1.5">
                            <i data-lucide="map-pin" class="w-3.5 h-3.5"></i>
                            <span>{{ $featuredEvent->locationLabel() }}</span>
                        </span>
                    </div>

                    <h1 class="text-3xl sm:text-5xl font-black leading-tight tracking-tight">
                        <a href="{{ route('magazine.events.show', $featuredEvent->slug) }}" class="hover:underline decoration-red-500">
                            {{ $featuredEvent->title }}
                        </a>
                    </h1>

                    @if($featuredEvent->subtitle)
                        <p class="text-base sm:text-lg text-amber-400 font-semibold">{{ $featuredEvent->subtitle }}</p>
                    @endif

                    <p class="text-sm text-slate-300 leading-relaxed max-w-2xl">
                        {{ $featuredEvent->excerpt }}
                    </p>

                    <div class="pt-3 flex flex-wrap items-center gap-4">
                        <a href="{{ route('magazine.events.show', $featuredEvent->slug) }}#event-registration" class="px-6 py-3.5 bg-red-700 hover:bg-red-800 text-white text-xs font-black uppercase tracking-wider rounded-xl inline-flex items-center gap-2 shadow-lg transition">
                            <i data-lucide="ticket" class="w-4 h-4"></i>
                            <span>S'inscrire à l'événement</span>
                        </a>
                        <a href="{{ route('magazine.events.show', $featuredEvent->slug) }}" class="px-5 py-3.5 bg-white/10 hover:bg-white/20 text-white text-xs font-bold uppercase tracking-wider rounded-xl inline-flex items-center gap-2 transition">
                            <span>Programme, Comité & Partenaires</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    </div>
                </div>

                <!-- Décompte Temps Réel jusqu'au Jour J -->
                <div class="lg:col-span-5 bg-slate-900/90 backdrop-blur-md border border-slate-700/80 rounded-2xl p-6 sm:p-8 text-center space-y-5">
                    <div class="space-y-1">
                        <span class="text-xs font-extrabold uppercase tracking-widest text-amber-400 block">Compte à rebours avant ouverture</span>
                        <span class="text-xs text-slate-400 block">
                            {{ $featuredEvent->starts_at->translatedFormat('l d F Y à H:i') }}
                        </span>
                    </div>

                    <div class="grid grid-cols-4 gap-3">
                        <div class="bg-slate-950 border border-slate-800 rounded-xl p-3">
                            <span class="text-2xl sm:text-3xl font-black text-white block font-mono" x-text="days">00</span>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Jours</span>
                        </div>
                        <div class="bg-slate-950 border border-slate-800 rounded-xl p-3">
                            <span class="text-2xl sm:text-3xl font-black text-white block font-mono" x-text="hours">00</span>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Heures</span>
                        </div>
                        <div class="bg-slate-950 border border-slate-800 rounded-xl p-3">
                            <span class="text-2xl sm:text-3xl font-black text-white block font-mono" x-text="minutes">00</span>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Minutes</span>
                        </div>
                        <div class="bg-slate-950 border border-slate-800 rounded-xl p-3">
                            <span class="text-2xl sm:text-3xl font-black text-amber-400 block font-mono" x-text="seconds">00</span>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Secondes</span>
                        </div>
                    </div>

                    <div class="text-xs text-slate-300 flex items-center justify-center gap-2">
                        <i data-lucide="users" class="w-4 h-4 text-emerald-400"></i>
                        <span>Capacité : <strong>{{ $featuredEvent->capacity }}</strong> décideurs IT</span>
                    </div>
                </div>
            </div>
        </section>
    @endif

    <!-- Liste des Événements -->
    <section class="space-y-6">
        <div class="flex items-center justify-between border-b-2 border-slate-900 pb-3">
            <h2 class="text-xl font-black uppercase tracking-tight text-slate-950 flex items-center gap-2">
                <i data-lucide="calendar" class="w-5 h-5 text-red-700"></i>
                <span>Agenda des Assises, Forums & Tables Rondes CIOMAG</span>
            </h2>
            <span class="text-xs font-semibold text-slate-500">Accréditations ouvertes</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            @foreach($events as $event)
                <article class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-2xs hover:shadow-md transition flex flex-col justify-between">
                    <div>
                        <a href="{{ route('magazine.events.show', $event->slug) }}" class="relative h-56 bg-slate-900 block overflow-hidden">
                            @if($event->cover_url)
                                <img src="{{ $event->cover_url }}" alt="{{ $event->title }}" class="w-full h-full object-cover hover:scale-105 transition duration-500">
                            @endif
                            <span class="absolute top-3 left-3 px-3 py-1 rounded-md bg-red-700 text-white text-[11px] font-bold uppercase">
                                {{ $event->event_type }}
                            </span>
                            <span class="absolute bottom-3 left-3 px-3 py-1 rounded-md bg-slate-950/90 text-amber-400 text-xs font-bold inline-flex items-center gap-1.5">
                                <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                                <span>{{ $event->starts_at->translatedFormat('d M Y') }}</span>
                            </span>
                        </a>

                        <div class="p-6 space-y-3">
                            <div class="flex items-center gap-2 text-xs text-slate-500">
                                <i data-lucide="map-pin" class="w-3.5 h-3.5 text-red-700"></i>
                                <span>{{ $event->locationLabel() }}</span>
                            </div>

                            <h3 class="text-xl font-extrabold text-slate-950 hover:text-red-700 leading-snug">
                                <a href="{{ route('magazine.events.show', $event->slug) }}">{{ $event->title }}</a>
                            </h3>

                            <p class="text-xs text-slate-600 line-clamp-2 leading-relaxed">
                                {{ $event->excerpt }}
                            </p>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-xs">
                        <span class="font-bold text-slate-700">
                            {{ count($event->program ?? []) }} sessions • {{ count($event->committee ?? []) }} intervenants
                        </span>
                        <a href="{{ route('magazine.events.show', $event->slug) }}" class="px-4 py-2 bg-slate-900 hover:bg-red-700 text-white font-bold rounded-xl inline-flex items-center gap-1.5 transition">
                            <span>Découvrir & S'inscrire</span>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </a>
                    </div>
                </article>
            @endforeach
        </div>

        <div>
            {{ $events->links() }}
        </div>
    </section>

</div>

<script>
    function eventCountdown(targetIso) {
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
