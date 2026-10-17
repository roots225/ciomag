@extends('layouts.magazine')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- HERO SECTION : À LA UNE -->
    @if($heroPost)
        <section class="mb-12">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">
                <!-- Grand Article À la Une (8 colonnes) -->
                <div class="lg:col-span-8 group relative bg-slate-900 rounded-2xl overflow-hidden shadow-lg flex flex-col justify-end min-h-[420px] sm:min-h-[480px]">
                    @if($heroPost->featuredImageUrl())
                        <img
                            src="{{ $heroPost->featuredImageUrl() }}"
                            alt="{{ $heroPost->featuredMedia?->alt_text ?? $heroPost->title }}"
                            class="absolute inset-0 w-full h-full object-cover opacity-55 group-hover:scale-105 transition duration-700"
                        >
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/70 to-transparent"></div>

                    <div class="relative p-6 sm:p-10 space-y-4">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="px-2.5 py-1 text-xs font-black uppercase tracking-wider bg-ciomag-pine text-white rounded-xs">
                                À la Une
                            </span>
                            @if($primaryCat = $heroPost->primaryCategory())
                                <a href="{{ route('magazine.category', $primaryCat->slug) }}" class="px-2.5 py-1 text-xs font-semibold uppercase tracking-wider bg-white/15 text-white backdrop-blur-xs rounded-xs hover:bg-white/25 transition">
                                    {{ $primaryCat->name }}
                                </a>
                            @endif
                            <span class="text-xs text-slate-300">
                                • {{ $heroPost->reading_time_minutes }} min de lecture
                            </span>
                        </div>

                        <h1 class="text-2xl sm:text-4xl font-extrabold text-white leading-tight tracking-tight">
                            <a href="{{ route('magazine.post', $heroPost->slug) }}" class="hover:underline decoration-ciomag-amber underline-offset-4">
                                {{ $heroPost->title }}
                            </a>
                        </h1>

                        @if($heroPost->excerpt)
                            <p class="text-slate-200 text-sm sm:text-base line-clamp-3 max-w-3xl">
                                {{ $heroPost->excerpt }}
                            </p>
                        @endif

                        <div class="pt-2 flex items-center justify-between text-xs text-slate-300 border-t border-white/15">
                            <div class="flex items-center gap-2.5">
                                @if($heroPost->author?->avatar_url)
                                    <img src="{{ $heroPost->author->avatar_url }}" alt="{{ $heroPost->author->display_name }}" class="w-7 h-7 rounded-full object-cover">
                                @endif
                                <span>Par <a href="{{ route('magazine.author', $heroPost->author->username) }}" class="font-semibold text-white hover:underline">{{ $heroPost->author->display_name }}</a></span>
                                <span>•</span>
                                <span>{{ $heroPost->published_at?->diffForHumans() }}</span>
                            </div>
                            <span class="hidden sm:inline font-medium text-ciomag-amber">
                                {{ number_format($heroPost->view_count, 0, ',', ' ') }} lectures
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Colonne Dossiers Épinglés / Baromètre (4 colonnes) -->
                <div class="lg:col-span-4 flex flex-col justify-between gap-6">
                    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs flex-1 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-5">
                                <h2 class="text-xs font-black uppercase tracking-wider text-ciomag-teal flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-ciomag-teal"></span>
                                    Grands Dossiers & Enquêtes
                                </h2>
                                <span class="text-[11px] font-medium text-slate-400">Édition 2026</span>
                            </div>

                            <div class="space-y-6">
                                @forelse($secondaryFeatured as $secPost)
                                    <article class="group">
                                        @if($cat = $secPost->primaryCategory())
                                            <a href="{{ route('magazine.category', $cat->slug) }}" class="text-[11px] font-bold uppercase tracking-wider text-ciomag-teal hover:text-ciomag-pine">
                                                {{ $cat->name }}
                                            </a>
                                        @endif
                                        <h3 class="mt-1 text-base font-bold text-slate-900 group-hover:text-ciomag-teal transition leading-snug">
                                            <a href="{{ route('magazine.post', $secPost->slug) }}">
                                                {{ $secPost->title }}
                                            </a>
                                        </h3>
                                        <p class="mt-1.5 text-xs text-slate-600 line-clamp-2">
                                            {{ $secPost->excerpt }}
                                        </p>
                                        <div class="mt-2 flex items-center gap-2 text-[11px] text-slate-400">
                                            <span>{{ $secPost->author->display_name }}</span>
                                            <span>•</span>
                                            <span>{{ $secPost->reading_time_minutes }} min</span>
                                        </div>
                                    </article>
                                @empty
                                    <p class="text-sm text-slate-500">Découvrez nos dernières analyses ci-dessous.</p>
                                @endforelse
                            </div>
                        </div>

                        <div class="mt-6 pt-4 border-t border-slate-100 bg-ciomag-teal-pale/50 -mx-6 -mb-6 p-5 rounded-b-2xl">
                            <p class="text-xs font-bold uppercase tracking-wider text-ciomag-pine">Vous êtes DSI, RSSI ou CDO ?</p>
                            <p class="text-xs text-slate-600 mt-1">Participez à nos prochaines tables rondes et baromètres sectoriels.</p>
                            <a href="{{ route('magazine.page', 'a-propos') }}" class="mt-2 inline-flex items-center gap-1 text-xs font-bold text-ciomag-teal hover:text-ciomag-pine">
                                En savoir plus sur le Club CIOMAG
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    <!-- RUBRIQUES RAPIDES -->
    <section class="mb-10 flex items-center gap-2 overflow-x-auto pb-2">
        <span class="text-xs font-bold uppercase tracking-wider text-slate-400 mr-2 shrink-0">Thématiques :</span>
        @foreach($categoriesWithPosts as $cat)
            <a
                href="{{ route('magazine.category', $cat->slug) }}"
                class="px-3.5 py-1.5 rounded-full text-xs font-semibold bg-white border border-slate-200 text-slate-700 hover:border-ciomag-teal hover:text-ciomag-teal hover:bg-ciomag-teal-pale/40 transition shrink-0"
            >
                {{ $cat->name }}
            </a>
        @endforeach
    </section>

    <!-- FLUX PRINCIPAL + SIDEBAR -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">

        <!-- Dernières Publications (8 colonnes) -->
        <div class="lg:col-span-8">
            <div class="flex items-center justify-between border-b-2 border-ciomag-slate-dark pb-3 mb-8">
                <h2 class="text-xl font-black tracking-tight text-slate-900 uppercase">
                    Dernières Analyses & Tribunes
                </h2>
                <span class="text-xs font-medium text-slate-500">Fil continu</span>
            </div>

            <div class="space-y-8">
                @foreach($latestPosts as $post)
                    <article class="bg-white rounded-2xl border border-slate-200/80 overflow-hidden shadow-2xs hover:shadow-md transition grid grid-cols-1 sm:grid-cols-12">
                        @if($post->featuredImageUrl())
                            <a href="{{ route('magazine.post', $post->slug) }}" class="sm:col-span-5 relative min-h-[200px] bg-slate-100 block overflow-hidden">
                                <img
                                    src="{{ $post->featuredImageUrl() }}"
                                    alt="{{ $post->featuredMedia?->alt_text ?? $post->title }}"
                                    class="w-full h-full object-cover hover:scale-105 transition duration-500"
                                >
                            </a>
                        @endif

                        <div class="{{ $post->featuredImageUrl() ? 'sm:col-span-7' : 'sm:col-span-12' }} p-6 flex flex-col justify-between">
                            <div>
                                <div class="flex flex-wrap items-center gap-2 mb-2">
                                    @if($post->is_premium)
                                        <span class="px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-wider bg-ciomag-pine text-ciomag-amber rounded-xs inline-flex items-center gap-1">
                                            <i data-lucide="crown" class="w-3 h-3"></i>
                                            <span>Premium</span>
                                        </span>
                                    @endif
                                    @if($primaryCat = $post->primaryCategory())
                                        <a href="{{ route('magazine.category', $primaryCat->slug) }}" class="text-xs font-bold uppercase tracking-wider text-ciomag-teal hover:text-ciomag-pine hover:underline">
                                            {{ $primaryCat->name }}
                                        </a>
                                    @endif
                                    <span class="text-slate-300">•</span>
                                    <span class="text-xs text-slate-500">{{ $post->reading_time_minutes }} min de lecture</span>
                                </div>

                                <h3 class="text-lg sm:text-xl font-bold text-slate-900 leading-snug hover:text-ciomag-teal transition">
                                    <a href="{{ route('magazine.post', $post->slug) }}">
                                        {{ $post->title }}
                                    </a>
                                </h3>

                                @if($post->excerpt)
                                    <p class="mt-2.5 text-sm text-slate-600 line-clamp-2 leading-relaxed">
                                        {{ $post->excerpt }}
                                    </p>
                                @endif
                            </div>

                            <div class="mt-5 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('magazine.author', $post->author->username) }}" class="font-semibold text-slate-800 hover:text-ciomag-teal">
                                        {{ $post->author->display_name }}
                                    </a>
                                    <span>•</span>
                                    <time datetime="{{ $post->published_at?->toIso8601String() }}">
                                        {{ $post->published_at?->translatedFormat('d M Y') }}
                                    </time>
                                </div>

                                <span class="text-slate-400">{{ number_format($post->view_count, 0, ',', ' ') }} vues</span>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $latestPosts->links() }}
            </div>
        </div>

        <!-- Sidebar Magazine (4 colonnes) -->
        <aside class="lg:col-span-4 space-y-8">
            <!-- Palmarès des articles les plus lus -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-2xs">
                <h3 class="text-sm font-black uppercase tracking-wider text-slate-900 border-b-2 border-ciomag-teal pb-2.5 mb-5">
                    Les Plus Lus de la Semaine
                </h3>

                <ol class="space-y-4">
                    @foreach($mostReadPosts as $index => $popular)
                        <li class="flex items-start gap-3.5 group">
                            <span class="text-2xl font-black text-slate-200 group-hover:text-ciomag-teal transition leading-none">
                                0{{ $index + 1 }}
                            </span>
                            <div>
                                @if($cat = $popular->primaryCategory())
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-ciomag-teal block">
                                        {{ $cat->name }}
                                    </span>
                                @endif
                                <a href="{{ route('magazine.post', $popular->slug) }}" class="text-sm font-bold text-slate-800 group-hover:text-ciomag-teal transition leading-snug block">
                                    {{ $popular->title }}
                                </a>
                                <span class="text-[11px] text-slate-400">
                                    {{ number_format($popular->view_count, 0, ',', ' ') }} lectures • {{ $popular->reading_time_minutes }} min
                                </span>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </div>

            <!-- Nuage de Tags Stratégiques -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-2xs">
                <h3 class="text-sm font-black uppercase tracking-wider text-slate-900 border-b border-slate-200 pb-2.5 mb-4">
                    Sujets & Technologies
                </h3>
                <div class="flex flex-wrap gap-2">
                    @foreach($popularTags as $tag)
                        <a
                            href="{{ route('magazine.tag', $tag->slug) }}"
                            class="px-3 py-1.5 text-xs font-medium bg-slate-100 hover:bg-ciomag-teal hover:text-white text-slate-700 rounded-lg transition"
                        >
                            #{{ $tag->name }}
                            <span class="text-[10px] opacity-70">({{ $tag->posts_count }})</span>
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Arborescence des Rubriques -->
            <div class="bg-[#182322] border border-ciomag-teal/30 text-white rounded-2xl p-6">
                <h3 class="text-sm font-black uppercase tracking-wider text-ciomag-amber mb-4">
                    Rubriques du Magazine
                </h3>
                <ul class="space-y-3 text-sm">
                    @foreach($categoriesWithPosts as $cat)
                        <li>
                            <a href="{{ route('magazine.category', $cat->slug) }}" class="font-semibold text-white hover:text-ciomag-amber flex items-center justify-between">
                                <span>{{ $cat->name }}</span>
                                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                            </a>
                            @if($cat->children->isNotEmpty())
                                <div class="mt-1.5 pl-3 border-l border-white/15 space-y-1">
                                    @foreach($cat->children as $sub)
                                        <a href="{{ route('magazine.category', $sub->slug) }}" class="block text-xs text-slate-300 hover:text-white">
                                            {{ $sub->name }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </aside>

    </div>

</div>
@endsection
