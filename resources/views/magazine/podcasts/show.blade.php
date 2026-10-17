@extends('layouts.magazine')

@section('meta_title', $podcast->title . ' — Podcast & WebTV CIOMAG')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-10">

    <div class="text-xs text-slate-500 flex items-center gap-2">
        <a href="{{ route('magazine.podcasts.index') }}" class="text-red-700 font-bold hover:underline inline-flex items-center gap-1">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
            <span>Tous les podcasts</span>
        </a>
        <span>/</span>
        <span>Saison {{ $podcast->season_number }} • Épisode {{ $podcast->episode_number }}</span>
    </div>

    <!-- Lecteur Vidéo (YouTube Embed ou Fichier Vidéo Hébergé) -->
    <div class="bg-slate-950 rounded-3xl overflow-hidden shadow-2xl border border-slate-800">
        <div class="aspect-video w-full bg-black">
            @if($podcast->source_type === 'youtube' && $podcast->embedYoutubeUrl())
                <iframe
                    src="{{ $podcast->embedYoutubeUrl() }}"
                    title="{{ $podcast->title }}"
                    class="w-full h-full"
                    frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen
                ></iframe>
            @elseif($podcast->videoFileUrl())
                <video controls poster="{{ $podcast->coverImage() }}" class="w-full h-full object-cover">
                    <source src="{{ $podcast->videoFileUrl() }}" type="video/mp4">
                </video>
            @else
                <img src="{{ $podcast->coverImage() }}" alt="{{ $podcast->title }}" class="w-full h-full object-cover">
            @endif
        </div>

        <div class="p-6 sm:p-8 text-white space-y-4">
            <div class="flex flex-wrap items-center gap-3 text-xs">
                <span class="px-3 py-1 rounded-full bg-red-700 text-white font-extrabold uppercase">
                    Saison {{ $podcast->season_number }} • Épisode {{ $podcast->episode_number }}
                </span>
                <span class="text-amber-400 font-bold inline-flex items-center gap-1">
                    <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                    <span>Durée : {{ $podcast->duration_label }}</span>
                </span>
                <span class="text-slate-400 inline-flex items-center gap-1">
                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                    <span>{{ number_format($podcast->view_count, 0, ',', ' ') }} vues</span>
                </span>
            </div>

            <h1 class="text-2xl sm:text-4xl font-black leading-tight">{{ $podcast->title }}</h1>

            @if($podcast->guest_name)
                <div class="pt-2 flex flex-wrap items-center gap-4 text-xs text-slate-300">
                    <span>Invité(s) : <strong class="text-white">{{ $podcast->guest_name }}</strong> ({{ $podcast->guest_role }})</span>
                    <span>•</span>
                    <span>Animé par : <strong class="text-amber-400">{{ $podcast->host_name }}</strong></span>
                </div>
            @endif
        </div>
    </div>

    <!-- Notes & Transcription de l'Épisode -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-2xs space-y-4">
        <h2 class="text-base font-black uppercase tracking-wider text-slate-950 border-b border-slate-100 pb-3 flex items-center gap-2">
            <i data-lucide="file-text" class="w-4 h-4 text-red-700"></i>
            <span>Notes & Synthèse de l'Épisode</span>
        </h2>
        @if($podcast->excerpt)
            <p class="text-sm font-semibold text-slate-700 leading-relaxed">{{ $podcast->excerpt }}</p>
        @endif
        <div class="article-content text-sm text-slate-600">
            {!! $podcast->description !!}
        </div>
    </div>

</div>
@endsection
