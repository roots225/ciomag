@extends('layouts.magazine')

@section('meta_title', $author->display_name . ' — Journaliste & Chroniqueur | CIOMAG')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <div class="bg-[#182322] border border-ciomag-teal/30 text-white rounded-2xl p-8 mb-10 flex flex-col sm:flex-row items-center sm:items-start gap-6 shadow-sm">
        @if($author->avatar_url)
            <img src="{{ $author->avatar_url }}" alt="{{ $author->display_name }}" class="w-24 h-24 rounded-full object-cover border-2 border-ciomag-amber shadow-sm">
        @endif
        <div>
            <span class="px-2.5 py-0.5 text-xs font-black uppercase bg-ciomag-pine text-white rounded-xs">
                {{ $author->role?->name ?? 'Rédaction' }}
            </span>
            <h1 class="mt-2 text-3xl font-black">{{ $author->display_name }}</h1>
            @if($author->bio)
                <p class="mt-2 text-slate-300 max-w-2xl text-sm leading-relaxed">{{ $author->bio }}</p>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @foreach($posts as $post)
            <article class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-2xs flex flex-col justify-between">
                <div>
                    @if($post->featuredImageUrl())
                        <a href="{{ route('magazine.post', $post->slug) }}" class="h-48 bg-slate-100 block overflow-hidden">
                            <img src="{{ $post->featuredImageUrl() }}" alt="{{ $post->title }}" class="w-full h-full object-cover">
                        </a>
                    @endif
                    <div class="p-6">
                        <h2 class="text-lg font-bold text-slate-900 hover:text-ciomag-teal leading-snug">
                            <a href="{{ route('magazine.post', $post->slug) }}">{{ $post->title }}</a>
                        </h2>
                        <p class="mt-2 text-sm text-slate-600 line-clamp-3">{{ $post->excerpt }}</p>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                    <span>{{ $post->published_at?->translatedFormat('d M Y') }}</span>
                    <span>{{ $post->reading_time_minutes }} min</span>
                </div>
            </article>
        @endforeach
    </div>

    <div class="mt-10">
        {{ $posts->links() }}
    </div>

</div>
@endsection
