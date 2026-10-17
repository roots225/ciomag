@extends('layouts.magazine')

@section('meta_title', '#' . $tag->name . ' — Dossiers & Articles | CIOMAG')
@section('meta_description', $tag->description ?: ('Tous les articles tagués #' . $tag->name . ' sur CIOMAG'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <div class="bg-white border border-slate-200 rounded-2xl p-8 mb-10 shadow-2xs">
        <span class="text-xs font-black uppercase tracking-wider text-ciomag-teal">Thématique & Mot-clé</span>
        <h1 class="mt-1 text-3xl font-black text-slate-900">#{{ $tag->name }}</h1>
        @if($tag->description)
            <p class="mt-2 text-slate-600 text-sm">{{ $tag->description }}</p>
        @endif
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
                    <span>{{ $post->author->display_name }}</span>
                    <span>{{ $post->published_at?->translatedFormat('d M Y') }}</span>
                </div>
            </article>
        @endforeach
    </div>

    <div class="mt-10">
        {{ $posts->links() }}
    </div>

</div>
@endsection
