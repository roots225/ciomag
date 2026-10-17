@extends('layouts.magazine')

@section('meta_title', $post->seoTitle())
@section('meta_description', $post->seoDescription())
@section('og_type', 'article')
@if(!empty($post->seo_meta['canonical_url']))
@section('canonical_url', $post->seo_meta['canonical_url'])
@endif
@if($post->featuredImageUrl())
@section('og_image', $post->featuredImageUrl())
@endif
@if(!empty($post->seo_meta['noindex']))
@section('meta_robots', 'noindex, nofollow')
@endif

@section('content')
    <article class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10" x-data="{ replyToId: null, replyToName: '' }">

        <!-- Fil d'Ariane (basé sur la catégorie principale is_primary) -->
        <nav class="text-xs text-slate-500 mb-6 flex flex-wrap items-center gap-2">
            <a href="{{ route('magazine.home') }}" class="hover:text-ciomag-teal">Accueil</a>
            <span>/</span>
            @if($primaryCategory)
                @if($primaryCategory->parent)
                    <a href="{{ route('magazine.category', $primaryCategory->parent->slug) }}" class="hover:text-ciomag-teal">
                        {{ $primaryCategory->parent->name }}
                    </a>
                    <span>/</span>
                @endif
                <a href="{{ route('magazine.category', $primaryCategory->slug) }}"
                    class="font-bold text-ciomag-teal hover:underline">
                    {{ $primaryCategory->name }}
                </a>
                <span>/</span>
            @endif
            <span class="text-slate-400 truncate max-w-xs">{{ $post->title }}</span>
        </nav>

        <!-- En-tête de l'article -->
        <header class="space-y-5 mb-8">
            <div class="flex flex-wrap items-center gap-2">
                @foreach($post->categories as $cat)
                    <a href="{{ route('magazine.category', $cat->slug) }}"
                        class="px-2.5 py-1 text-xs font-bold uppercase tracking-wider rounded-xs {{ $cat->pivot->is_primary ? 'bg-ciomag-pine text-white' : 'bg-slate-200 text-slate-700 hover:bg-slate-300' }}">
                        {{ $cat->name }}
                    </a>
                @endforeach
                @if($post->is_featured)
                    <span
                        class="px-2.5 py-1 text-xs font-bold uppercase tracking-wider bg-amber-100 text-amber-800 rounded-xs inline-flex items-center gap-1">
                        <i data-lucide="star" class="w-3.5 h-3.5 fill-amber-500 text-amber-500"></i>
                        <span>Grand Dossier</span>
                    </span>
                @endif
                @if($post->is_premium)
                    <span
                        class="px-2.5 py-1 text-xs font-extrabold uppercase tracking-wider bg-ciomag-pine text-ciomag-amber rounded-xs inline-flex items-center gap-1.5 border border-ciomag-amber/40">
                        <i data-lucide="crown" class="w-3.5 h-3.5"></i>
                        <span>Exclusif Abonnés Premium</span>
                    </span>
                @endif
            </div>

            <h1 class="text-3xl sm:text-5xl font-black text-slate-950 leading-tight tracking-tight">
                {{ $post->title }}
            </h1>

            @if($post->excerpt)
                <p class="text-lg sm:text-xl text-slate-600 leading-relaxed font-normal">
                    {{ $post->excerpt }}
                </p>
            @endif

            <!-- Signature & Métriques -->
            <div class="pt-4 border-y border-slate-200 py-4 flex flex-wrap items-center justify-between gap-4 text-sm">
                <div class="flex items-center gap-3">
                    @if($post->author?->avatar_url)
                        <img src="{{ $post->author->avatar_url }}" alt="{{ $post->author->display_name }}"
                            class="w-11 h-11 rounded-full object-cover border border-slate-200">
                    @else
                        <div
                            class="w-11 h-11 rounded-full bg-ciomag-pine text-white flex items-center justify-center font-bold text-sm">
                            {{ $post->author?->initials() }}
                        </div>
                    @endif
                    <div>
                        <a href="{{ route('magazine.author', $post->author->username) }}"
                            class="font-bold text-slate-900 hover:text-ciomag-teal">
                            {{ $post->author->display_name }}
                        </a>
                        <p class="text-xs text-slate-500">
                            {{ $post->author->role?->name ?? 'Rédaction CIOMAG' }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-4 text-xs text-slate-500">
                    <span class="inline-flex items-center gap-1">
                        <i data-lucide="calendar" class="w-3.5 h-3.5 text-slate-400"></i>
                        <span>Publié le <strong
                                class="text-slate-700">{{ $post->published_at?->translatedFormat('d F Y') }}</strong></span>
                    </span>
                    <span>•</span>
                    <span class="inline-flex items-center gap-1">
                        <i data-lucide="clock" class="w-3.5 h-3.5 text-slate-400"></i>
                        <span>{{ $post->reading_time_minutes }} min de lecture</span>
                    </span>
                    <span>•</span>
                    <span class="inline-flex items-center gap-1">
                        <i data-lucide="eye" class="w-3.5 h-3.5 text-slate-400"></i>
                        <span>{{ number_format($post->view_count, 0, ',', ' ') }} lectures</span>
                    </span>
                </div>
            </div>
        </header>

        <!-- Image de couverture avec légende -->
        @if($post->featuredImageUrl())
            <figure class="mb-10">
                <div class="rounded-2xl overflow-hidden bg-slate-100 border border-slate-200 shadow-sm">
                    <img src="{{ $post->featuredImageUrl() }}" alt="{{ $post->featuredMedia?->alt_text ?? $post->title }}"
                        class="w-full max-h-[520px] object-cover">
                </div>
                @if($post->featuredMedia?->caption)
                    <figcaption class="mt-2.5 text-xs text-slate-500 italic text-center">
                        {{ $post->featuredMedia->caption }}
                    </figcaption>
                @endif
            </figure>
        @endif

        @php
            $canReadFullArticle = !$post->is_premium || (auth()->check() && auth()->user()->canReadPremiumContent());
        @endphp

        <!-- Corps de l'article (ou Aperçu + Paywall si article Premium verrouillé) -->
        <div
            class="article-content bg-white p-6 sm:p-10 rounded-2xl border border-slate-200/80 shadow-2xs relative overflow-hidden">
            @if($canReadFullArticle)
                @if($post->is_premium)
                    <div
                        class="mb-6 px-4 py-2.5 rounded-xl bg-ciomag-amber-light border border-ciomag-amber/30 text-amber-900 text-xs font-semibold flex items-center gap-2">
                        <i data-lucide="unlock" class="w-4 h-4 text-ciomag-amber shrink-0"></i>
                        <span>Édition Abonné déverrouillée — Merci pour votre soutien à la rédaction indépendante de CIOMAG.</span>
                    </div>
                @endif

                {!! $post->content !!}
            @else
                <div class="relative">
                    {!! $post->previewContent() !!}
                    <div class="h-32 bg-gradient-to-t from-white via-white/90 to-transparent -mt-24 relative z-10"></div>
                </div>

                <!-- Paywall CIOMAG Premium -->
                <div id="premium-paywall"
                    class="relative z-20 mt-2 bg-[#182322] text-white rounded-2xl p-8 sm:p-10 border border-ciomag-amber/40 shadow-xl">
                    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                        <div class="space-y-3 max-w-xl">
                            <span
                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-ciomag-amber text-slate-950 text-xs font-extrabold uppercase tracking-wider">
                                <i data-lucide="crown" class="w-3.5 h-3.5"></i>
                                <span>Article Exclusif Abonnés</span>
                            </span>
                            <h3 class="text-2xl sm:text-3xl font-black leading-tight text-white">
                                La suite de cette enquête est réservée aux abonnés CIOMAG Premium
                            </h3>
                            <p class="text-sm text-slate-300 leading-relaxed">
                                Débloquez immédiatement cet article, nos baromètres sectoriels DSI/RSSI, nos livres blancs et
                                les éditions bimestrielles du magazine à partir de <strong class="text-ciomag-amber">14,90 € /
                                    mois</strong>.
                            </p>
                            <ul class="pt-1 grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs text-slate-300">
                                <li class="flex items-center gap-2">
                                    <i data-lucide="check-circle-2" class="w-4 h-4 text-ciomag-amber shrink-0"></i>
                                    <span>Accès illimité aux articles exclusifs</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <i data-lucide="check-circle-2" class="w-4 h-4 text-ciomag-amber shrink-0"></i>
                                    <span>Magazines bimestriels PDF HD</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <i data-lucide="check-circle-2" class="w-4 h-4 text-ciomag-amber shrink-0"></i>
                                    <span>Études & Benchmarks Budgets IT</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <i data-lucide="check-circle-2" class="w-4 h-4 text-ciomag-amber shrink-0"></i>
                                    <span>Invitations VIP aux Assises CIOMAG</span>
                                </li>
                            </ul>
                        </div>

                        <div class="w-full md:w-auto flex flex-col gap-3 shrink-0">
                            <a href="{{ route('magazine.subscriptions.index') }}"
                                class="px-6 py-3.5 bg-ciomag-amber hover:bg-ciomag-amber-hover text-slate-950 text-sm font-black uppercase tracking-wider rounded-xl text-center inline-flex items-center justify-center gap-2 shadow-lg transition">
                                <i data-lucide="sparkles" class="w-4 h-4"></i>
                                <span>S'abonner maintenant</span>
                            </a>
                            @guest
                                <a href="{{ route('login') }}"
                                    class="px-6 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-xl text-center inline-flex items-center justify-center gap-1.5 transition">
                                    <i data-lucide="log-in" class="w-3.5 h-3.5"></i>
                                    <span>Déjà abonné ? Se connecter</span>
                                </a>
                            @endguest
                        </div>
                    </div>
                </div>
            @endif

            <!-- Tags -->
            @if($post->tags->isNotEmpty())
                <div class="mt-10 pt-6 border-t border-slate-100 flex flex-wrap items-center gap-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400 mr-2">Mots-clés :</span>
                    @foreach($post->tags as $tag)
                        <a href="{{ route('magazine.tag', $tag->slug) }}"
                            class="px-3 py-1 text-xs font-semibold bg-slate-100 hover:bg-ciomag-teal hover:text-white text-slate-700 rounded-full transition">
                            #{{ $tag->name }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Encadré Auteur -->
        <section
            class="mt-10 bg-[#182322] border border-ciomag-teal/30 text-white rounded-2xl p-6 sm:p-8 flex flex-col sm:flex-row items-start gap-5">
            @if($post->author?->avatar_url)
                <img src="{{ $post->author->avatar_url }}" alt="{{ $post->author->display_name }}"
                    class="w-16 h-16 rounded-full object-cover shrink-0 border-2 border-ciomag-amber">
            @endif
            <div class="space-y-2">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-xs uppercase tracking-wider font-bold text-ciomag-amber">À propos de l'auteur</span>
                    <span class="text-slate-500">•</span>
                    <a href="{{ route('magazine.author', $post->author->username) }}"
                        class="text-lg font-bold text-white hover:underline">
                        {{ $post->author->display_name }}
                    </a>
                </div>
                <p class="text-sm text-slate-300 leading-relaxed">
                    {{ $post->author->bio ?? 'Journaliste et analyste au sein de la rédaction de CIOMAG.' }}
                </p>
                @if(!empty($post->author->social_links))
                    <div class="pt-1 flex items-center gap-4 text-xs text-ciomag-amber font-semibold">
                        @foreach($post->author->social_links as $network => $link)
                            <a href="{{ $link }}" target="_blank" rel="noopener" class="hover:underline capitalize">
                                {{ $network }} ↗
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>

        <!-- Articles Connexes -->
        @if($relatedPosts->isNotEmpty())
            <section class="mt-12">
                <h2 class="text-lg font-black uppercase tracking-wider text-slate-900 border-b-2 border-ciomag-teal pb-2 mb-6">
                    Sur le même sujet
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach($relatedPosts as $rel)
                        <article
                            class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-2xs flex flex-col justify-between">
                            @if($rel->featuredImageUrl())
                                <a href="{{ route('magazine.post', $rel->slug) }}" class="h-40 bg-slate-100 block overflow-hidden">
                                    <img src="{{ $rel->featuredImageUrl() }}" alt="{{ $rel->title }}"
                                        class="w-full h-full object-cover hover:scale-105 transition">
                                </a>
                            @endif
                            <div class="p-4 flex-1 flex flex-col justify-between">
                                <h3 class="text-sm font-bold text-slate-900 hover:text-ciomag-teal leading-snug">
                                    <a href="{{ route('magazine.post', $rel->slug) }}">{{ $rel->title }}</a>
                                </h3>
                                <div class="mt-3 text-[11px] text-slate-400">
                                    {{ $rel->published_at?->translatedFormat('d M Y') }} • {{ $rel->reading_time_minutes }} min
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        <!-- Section Commentaires Imbriqués -->
        <section id="comments" class="mt-14 pt-10 border-t border-slate-200">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-black text-slate-900">
                    Commentaires ({{ $post->approvedComments()->count() }})
                </h2>
                <span class="text-xs text-slate-500">Modération par la rédaction</span>
            </div>

            <!-- Liste des commentaires approuvés -->
            <div class="space-y-5 mb-10">
                @forelse($comments as $comment)
                    <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-2xs">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-sm text-slate-900">{{ $comment->authorName() }}</span>
                                @if($comment->user?->role)
                                    <span class="px-2 py-0.5 text-[10px] font-bold uppercase bg-slate-900 text-white rounded-xs">
                                        {{ $comment->user->role->name }}
                                    </span>
                                @endif
                            </div>
                            <span class="text-xs text-slate-400">{{ $comment->created_at->diffForHumans() }}</span>
                        </div>

                        <p class="text-sm text-slate-700 leading-relaxed">{{ $comment->content }}</p>

                        <div class="mt-3">
                            <button type="button"
                                @click="replyToId = '{{ $comment->id }}'; replyToName = '{{ addslashes($comment->authorName()) }}'; document.getElementById('comment-form').scrollIntoView({behavior: 'smooth'})"
                                class="text-xs font-semibold text-ciomag-teal hover:underline inline-flex items-center gap-1 cursor-pointer">
                                <i data-lucide="corner-down-right" class="w-3.5 h-3.5"></i>
                                <span>Répondre</span>
                            </button>
                        </div>

                        <!-- Réponses imbriquées (Niveau 2) -->
                        @if($comment->approvedReplies->isNotEmpty())
                            <div class="mt-4 pl-4 sm:pl-6 border-l-2 border-slate-200 space-y-4">
                                @foreach($comment->approvedReplies as $reply)
                                    <div class="bg-slate-50 rounded-lg p-4">
                                        <div class="flex items-center justify-between mb-1.5">
                                            <div class="flex items-center gap-2">
                                                <span class="font-bold text-xs text-slate-900">{{ $reply->authorName() }}</span>
                                                @if($reply->user?->role)
                                                    <span
                                                        class="px-1.5 py-0.5 text-[10px] font-bold uppercase bg-ciomag-teal text-white rounded-xs">
                                                        {{ $reply->user->role->name }}
                                                    </span>
                                                @endif
                                            </div>
                                            <span class="text-[11px] text-slate-400">{{ $reply->created_at->diffForHumans() }}</span>
                                        </div>
                                        <p class="text-sm text-slate-700">{{ $reply->content }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="bg-white rounded-xl border border-slate-200 p-6 text-center text-sm text-slate-500">
                        Aucun commentaire pour le moment. Ouvrez le débat ci-dessous !
                    </div>
                @endforelse
            </div>

            @if($comments->total() > 0)
                <div class="mb-10">
                    {{ $comments->links() }}
                </div>
            @endif

            <!-- Formulaire de commentaire -->
            @if($post->allow_comments)
                <div id="comment-form" class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-2xs">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                            <i data-lucide="message-square-plus" class="w-4 h-4 text-ciomag-teal"></i>
                            <span>Contribuer à la discussion</span>
                        </h3>
                        <div x-show="replyToId" x-cloak
                            class="flex items-center gap-2 text-xs bg-amber-50 text-amber-800 px-3 py-1 rounded-full">
                            <span>En réponse à <strong x-text="replyToName"></strong></span>
                            <button type="button" @click="replyToId = null; replyToName = ''"
                                class="font-bold hover:text-ciomag-teal">
                                <i data-lucide="x" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>
                    </div>

                    <form action="{{ route('magazine.comments.store', $post->slug) }}" method="POST" class="space-y-4">
                        @csrf
                        <input type="hidden" name="parent_id" :value="replyToId">

                        @guest
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Votre nom & fonction *</label>
                                    <input type="text" name="guest_name" value="{{ old('guest_name') }}" required
                                        placeholder="Ex: Jean Koné (DSI)"
                                        class="w-full px-3.5 py-2 text-sm border border-slate-300 rounded-lg focus:outline-none focus:border-ciomag-teal focus:ring-1 focus:ring-ciomag-teal">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Email professionnel *</label>
                                    <input type="email" name="guest_email" value="{{ old('guest_email') }}" required
                                        placeholder="nom@entreprise.com"
                                        class="w-full px-3.5 py-2 text-sm border border-slate-300 rounded-lg focus:outline-none focus:border-ciomag-teal focus:ring-1 focus:ring-ciomag-teal">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Site web / Organisation</label>
                                    <input type="url" name="guest_website" value="{{ old('guest_website') }}"
                                        placeholder="https://..."
                                        class="w-full px-3.5 py-2 text-sm border border-slate-300 rounded-lg focus:outline-none focus:border-ciomag-teal focus:ring-1 focus:ring-ciomag-teal">
                                </div>
                            </div>
                        @else
                            <p class="text-xs text-slate-600">
                                Connecté en tant que <strong>{{ auth()->user()->display_name }}</strong>
                                ({{ auth()->user()->role?->name }}). Votre commentaire sera publié immédiatement.
                            </p>
                        @endguest

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Votre analyse ou réaction *</label>
                            <textarea name="content" rows="4" required
                                placeholder="Partagez votre retour d'expérience avec la communauté CIOMAG..."
                                class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-lg focus:outline-none focus:border-ciomag-teal focus:ring-1 focus:ring-ciomag-teal">{{ old('content') }}</textarea>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-xs text-slate-400">Contrainte d'intégrité : utilisateur identifié ou couple
                                nom/email valide requis.</span>
                            <button type="submit"
                                class="px-5 py-2.5 bg-ciomag-teal hover:bg-ciomag-pine text-white text-sm font-bold rounded-lg transition cursor-pointer">
                                Publier le commentaire
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        </section>

    </article>
@endsection