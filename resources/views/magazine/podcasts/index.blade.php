@extends('layouts.magazine')

@section('meta_title', 'Podcasts & WebTV CIOMAG — Entretiens Exclusifs DSI & Tech')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-12">

    <!-- Épisode à la Une (Lecteur YouTube ou Vidéo Locale) -->
    @if($featuredPodcast)
        <section class="bg-slate-950 text-white rounded-3xl overflow-hidden shadow-xl border border-slate-800 p-6 sm:p-10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                <div class="lg:col-span-7">
                    <div class="aspect-video rounded-2xl overflow-hidden bg-black border border-slate-800 shadow-2xl">
                        @if($featuredPodcast->source_type === 'youtube' && $featuredPodcast->embedYoutubeUrl())
                            <iframe
                                src="{{ $featuredPodcast->embedYoutubeUrl() }}"
                                title="{{ $featuredPodcast->title }}"
                                class="w-full h-full"
                                frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen
                            ></iframe>
                        @elseif($featuredPodcast->videoFileUrl())
                            <video controls poster="{{ $featuredPodcast->coverImage() }}" class="w-full h-full object-cover">
                                <source src="{{ $featuredPodcast->videoFileUrl() }}" type="video/mp4">
                            </video>
                        @else
                            <img src="{{ $featuredPodcast->coverImage() }}" alt="{{ $featuredPodcast->title }}" class="w-full h-full object-cover">
                        @endif
                    </div>
                </div>

                <div class="lg:col-span-5 space-y-4">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="px-3 py-1 rounded-full bg-ciomag-teal text-white text-xs font-extrabold uppercase tracking-wider inline-flex items-center gap-1.5">
                            <i data-lucide="mic" class="w-3.5 h-3.5"></i>
                            <span>Saison {{ $featuredPodcast->season_number }} • Épisode {{ $featuredPodcast->episode_number }}</span>
                        </span>
                        <span class="px-2.5 py-1 rounded-full bg-white/10 text-ciomag-amber text-xs font-bold inline-flex items-center gap-1">
                            <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                            <span>{{ $featuredPodcast->duration_label }}</span>
                        </span>
                    </div>

                    <h1 class="text-2xl sm:text-3xl font-black leading-tight">
                        <a href="{{ route('magazine.podcasts.show', $featuredPodcast->slug) }}" class="hover:text-ciomag-amber transition">
                            {{ $featuredPodcast->title }}
                        </a>
                    </h1>

                    @if($featuredPodcast->guest_name)
                        <div class="p-3.5 rounded-xl bg-white/5 border border-white/10 text-xs">
                            <span class="text-slate-400 uppercase font-bold block text-[10px]">Invité(s) de l'émission :</span>
                            <span class="font-extrabold text-white text-sm">{{ $featuredPodcast->guest_name }}</span>
                            @if($featuredPodcast->guest_role)
                                <span class="text-ciomag-amber block">{{ $featuredPodcast->guest_role }}</span>
                            @endif
                        </div>
                    @endif

                    <p class="text-xs sm:text-sm text-slate-300 leading-relaxed line-clamp-3">
                        {{ $featuredPodcast->excerpt }}
                    </p>

                    <div class="pt-2">
                        <a href="{{ route('magazine.podcasts.show', $featuredPodcast->slug) }}" class="px-5 py-3 bg-ciomag-teal hover:bg-ciomag-pine text-white text-xs font-black uppercase tracking-wider rounded-xl inline-flex items-center gap-2 transition">
                            <i data-lucide="play" class="w-4 h-4"></i>
                            <span>Voir la fiche complète de l'épisode</span>
                        </a>
                    </div>
                </div>
            </div>
        </section>
    @endif

    <!-- Filtres & Grille des Épisodes -->
    <section class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4 border-b-2 border-ciomag-teal pb-3">
            <h2 class="text-xl font-black uppercase tracking-tight text-slate-950 flex items-center gap-2">
                <i data-lucide="tv" class="w-5 h-5 text-ciomag-teal"></i>
                <span>Tous les Épisodes Podcasts & WebTV CIOMAG</span>
            </h2>

            <div class="flex items-center gap-2">
                <a href="{{ route('magazine.podcasts.index') }}" class="px-3.5 py-1.5 rounded-xl text-xs font-bold uppercase {{ !$source ? 'bg-ciomag-teal text-white' : 'bg-white border border-slate-200 text-slate-700' }}">
                    Tous
                </a>
                <a href="{{ route('magazine.podcasts.index', ['source' => 'youtube']) }}" class="px-3.5 py-1.5 rounded-xl text-xs font-bold uppercase inline-flex items-center gap-1 {{ $source === 'youtube' ? 'bg-ciomag-teal text-white' : 'bg-white border border-slate-200 text-slate-700' }}">
                    <i data-lucide="youtube" class="w-3.5 h-3.5"></i>
                    <span>YouTube</span>
                </a>
                <a href="{{ route('magazine.podcasts.index', ['source' => 'upload']) }}" class="px-3.5 py-1.5 rounded-xl text-xs font-bold uppercase inline-flex items-center gap-1 {{ $source === 'upload' ? 'bg-ciomag-teal text-white' : 'bg-white border border-slate-200 text-slate-700' }}">
                    <i data-lucide="video" class="w-3.5 h-3.5"></i>
                    <span>Studio CIOMAG</span>
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($podcasts as $ep)
                <article class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-2xs hover:shadow-md transition flex flex-col justify-between group">
                    <div>
                        <a href="{{ route('magazine.podcasts.show', $ep->slug) }}" class="relative h-48 bg-slate-900 block overflow-hidden">
                            <img src="{{ $ep->coverImage() }}" alt="{{ $ep->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                            <div class="absolute inset-0 bg-slate-950/30 flex items-center justify-center">
                                <span class="w-12 h-12 rounded-full bg-ciomag-teal text-white flex items-center justify-center shadow-lg group-hover:scale-110 transition">
                                    <i data-lucide="play" class="w-5 h-5 ml-0.5"></i>
                                </span>
                            </div>
                            <span class="absolute top-3 left-3 px-2.5 py-1 text-[10px] font-extrabold uppercase bg-slate-950/90 text-white rounded-md">
                                S{{ $ep->season_number }} • E{{ $ep->episode_number }}
                            </span>
                            <span class="absolute bottom-3 right-3 px-2 py-0.5 text-[11px] font-bold bg-slate-950/90 text-ciomag-amber rounded-md">
                                {{ $ep->duration_label }}
                            </span>
                        </a>

                        <div class="p-6 space-y-2.5">
                            @if($ep->guest_name)
                                <div class="text-xs font-bold text-ciomag-teal flex items-center gap-1.5">
                                    <i data-lucide="user-check" class="w-3.5 h-3.5"></i>
                                    <span>{{ $ep->guest_name }}</span>
                                </div>
                            @endif

                            <h3 class="text-base font-extrabold text-slate-900 group-hover:text-ciomag-teal leading-snug">
                                <a href="{{ route('magazine.podcasts.show', $ep->slug) }}">{{ $ep->title }}</a>
                            </h3>

                            <p class="text-xs text-slate-600 line-clamp-2 leading-relaxed">
                                {{ $ep->excerpt }}
                            </p>
                        </div>
                    </div>

                    <div class="px-6 py-3.5 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                        <span class="inline-flex items-center gap-1">
                            <i data-lucide="{{ $ep->source_type === 'youtube' ? 'youtube' : 'video' }}" class="w-3.5 h-3.5 text-ciomag-teal"></i>
                            <span>{{ $ep->source_type === 'youtube' ? 'YouTube' : 'Vidéo Plateforme' }}</span>
                        </span>
                        <span>{{ number_format($ep->view_count, 0, ',', ' ') }} écoutes</span>
                    </div>
                </article>
            @endforeach
        </div>

        <div>
            {{ $podcasts->links() }}
        </div>
    </section>

</div>
@endsection
