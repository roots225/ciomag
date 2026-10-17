@extends('layouts.magazine')

@section('meta_title', 'Recherche : ' . ($query ?: 'Tous les dossiers') . ' | CIOMAG')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 mb-8">
        <span class="text-xs font-bold uppercase tracking-wider text-red-700">Indexation Plein Texte (Full-Text Search)</span>
        <h1 class="mt-1 text-2xl sm:text-3xl font-black text-slate-900">Rechercher dans les archives CIOMAG</h1>

        <form action="{{ route('magazine.search') }}" method="GET" class="mt-4 flex gap-3">
            <input
                type="search"
                name="q"
                value="{{ $query }}"
                placeholder="Ex: IA Générative, Zero Trust, Data Centers, Open Banking..."
                class="flex-1 px-4 py-2.5 text-sm border border-slate-300 rounded-xl focus:outline-none focus:border-red-700"
            >
            <button type="submit" class="px-6 py-2.5 bg-red-700 hover:bg-red-800 text-white text-sm font-bold rounded-xl transition cursor-pointer inline-flex items-center gap-2">
                <i data-lucide="search" class="w-4 h-4"></i>
                Rechercher
            </button>
        </form>
    </div>

    @if($query !== '')
        <p class="text-sm text-slate-600 mb-6">
            Résultats pour <strong class="text-slate-900">« {{ $query }} »</strong> :
            <strong>{{ $posts->total() }}</strong> article(s) trouvé(s).
        </p>

        <div class="space-y-6">
            @forelse($posts as $post)
                <article class="bg-white rounded-xl border border-slate-200 p-6 hover:shadow-sm transition">
                    <div class="flex items-center gap-2 text-xs text-slate-500 mb-1.5">
                        @if($cat = $post->primaryCategory())
                            <span class="font-bold text-red-700 uppercase">{{ $cat->name }}</span>
                            <span>•</span>
                        @endif
                        <span>{{ $post->published_at?->translatedFormat('d M Y') }}</span>
                        <span>•</span>
                        <span>{{ $post->reading_time_minutes }} min de lecture</span>
                    </div>
                    <h2 class="text-xl font-bold text-slate-900 hover:text-red-700">
                        <a href="{{ route('magazine.post', $post->slug) }}">{{ $post->title }}</a>
                    </h2>
                    <p class="mt-2 text-sm text-slate-600">{{ $post->excerpt }}</p>
                </article>
            @empty
                <div class="bg-white rounded-xl border border-slate-200 p-10 text-center text-slate-500">
                    Aucun article ne correspond à votre recherche « {{ $query }} ».
                </div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $posts->links() }}
        </div>
    @endif

</div>
@endsection
