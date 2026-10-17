@extends('layouts.magazine')

@section('meta_title', ($category->seo_meta['meta_title'] ?? ($category->name . ' — Rubrique CIOMAG')))
@section('meta_description', ($category->seo_meta['meta_description'] ?? ($category->description ?: 'Articles de la rubrique ' . $category->name)))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <!-- En-tête de la Catégorie -->
    <div class="bg-slate-900 text-white rounded-2xl p-8 sm:p-10 mb-10">
        <div class="flex items-center gap-2 text-xs text-amber-400 font-bold uppercase tracking-wider mb-2">
            <a href="{{ route('magazine.home') }}" class="hover:underline">Accueil</a>
            <span>/</span>
            @if($category->parent)
                <a href="{{ route('magazine.category', $category->parent->slug) }}" class="hover:underline">{{ $category->parent->name }}</a>
                <span>/</span>
            @endif
            <span>Rubrique</span>
        </div>

        <h1 class="text-3xl sm:text-4xl font-black tracking-tight">{{ $category->name }}</h1>
        @if($category->description)
            <p class="mt-3 text-slate-300 max-w-3xl text-base leading-relaxed">{{ $category->description }}</p>
        @endif

        @if($category->children->isNotEmpty())
            <div class="mt-6 pt-5 border-t border-slate-800 flex flex-wrap items-center gap-2">
                <span class="text-xs text-slate-400 font-semibold mr-2">Sous-rubriques :</span>
                @foreach($category->children as $sub)
                    <a href="{{ route('magazine.category', $sub->slug) }}" class="px-3 py-1 text-xs font-semibold bg-slate-800 hover:bg-red-700 text-white rounded-full transition">
                        {{ $sub->name }}
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Grille des articles -->
    @if($posts->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center text-slate-500">
            Aucun article publié dans cette rubrique pour le moment.
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($posts as $post)
                <article class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-2xs hover:shadow-md transition flex flex-col justify-between">
                    <div>
                        @if($post->featuredImageUrl())
                            <a href="{{ route('magazine.post', $post->slug) }}" class="h-48 bg-slate-100 block overflow-hidden">
                                <img src="{{ $post->featuredImageUrl() }}" alt="{{ $post->title }}" class="w-full h-full object-cover hover:scale-105 transition duration-500">
                            </a>
                        @endif
                        <div class="p-6">
                            <div class="flex items-center gap-2 text-xs text-slate-500 mb-2">
                                <span class="font-bold text-red-700 uppercase">{{ $post->primaryCategory()?->name ?? $category->name }}</span>
                                <span>•</span>
                                <span>{{ $post->reading_time_minutes }} min</span>
                            </div>
                            <h2 class="text-lg font-bold text-slate-900 hover:text-red-700 leading-snug">
                                <a href="{{ route('magazine.post', $post->slug) }}">{{ $post->title }}</a>
                            </h2>
                            @if($post->excerpt)
                                <p class="mt-2 text-sm text-slate-600 line-clamp-3">{{ $post->excerpt }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                        <a href="{{ route('magazine.author', $post->author->username) }}" class="font-semibold text-slate-800 hover:text-red-700">
                            {{ $post->author->display_name }}
                        </a>
                        <span>{{ $post->published_at?->translatedFormat('d M Y') }}</span>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-10">
            {{ $posts->links() }}
        </div>
    @endif

</div>
@endsection
