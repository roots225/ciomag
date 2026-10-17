@extends('layouts.magazine')

@section('meta_title', 'Boutique Officielle CIOMAG — Magazines, Livres & Études DSI')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-10">

    <!-- Bannière Hero Boutique -->
    <div class="bg-slate-950 text-white rounded-3xl p-8 sm:p-12 relative overflow-hidden border border-slate-800">
        <div class="relative z-10 max-w-3xl space-y-4">
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-700 text-white text-xs font-extrabold uppercase tracking-wider">
                <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i>
                <span>Kiosque & Librairie Exécutive CIOMAG</span>
            </span>
            <h1 class="text-3xl sm:text-5xl font-black tracking-tight leading-tight">
                Magazines Bimestriels, Ouvrages & Baromètres Sectoriels
            </h1>
            <p class="text-slate-300 text-sm sm:text-base leading-relaxed">
                Commandez les numéros collectors de CIOMAG (édition papier ou numérique PDF HD), nos ouvrages méthodologiques pour DSI et nos études exclusives sur le Cloud souverain, l'IA et la cybersécurité.
            </p>
        </div>
    </div>

    <!-- Barre de filtres par catégorie de produit -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 flex flex-wrap items-center justify-between gap-4 shadow-2xs">
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('magazine.shop.index') }}" class="px-4 py-2 rounded-xl text-xs font-bold uppercase inline-flex items-center gap-1.5 transition {{ !$type ? 'bg-slate-900 text-white' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">
                <i data-lucide="grid" class="w-3.5 h-3.5"></i>
                <span>Tout le catalogue</span>
            </a>
            <a href="{{ route('magazine.shop.index', ['type' => 'magazine']) }}" class="px-4 py-2 rounded-xl text-xs font-bold uppercase inline-flex items-center gap-1.5 transition {{ $type === 'magazine' ? 'bg-red-700 text-white' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">
                <i data-lucide="book-open" class="w-3.5 h-3.5"></i>
                <span>Magazines</span>
            </a>
            <a href="{{ route('magazine.shop.index', ['type' => 'book']) }}" class="px-4 py-2 rounded-xl text-xs font-bold uppercase inline-flex items-center gap-1.5 transition {{ $type === 'book' ? 'bg-red-700 text-white' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">
                <i data-lucide="library" class="w-3.5 h-3.5"></i>
                <span>Livres & Guides</span>
            </a>
            <a href="{{ route('magazine.shop.index', ['type' => 'report']) }}" class="px-4 py-2 rounded-xl text-xs font-bold uppercase inline-flex items-center gap-1.5 transition {{ $type === 'report' ? 'bg-red-700 text-white' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">
                <i data-lucide="bar-chart-3" class="w-3.5 h-3.5"></i>
                <span>Études & Livres Blancs</span>
            </a>
        </div>

        <form action="{{ route('magazine.shop.index') }}" method="GET" class="flex items-center gap-2">
            @if($type)
                <input type="hidden" name="type" value="{{ $type }}">
            @endif
            <div class="relative">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
                <input type="search" name="q" value="{{ $search }}" placeholder="Numéro, titre, sujet..." class="pl-9 pr-3 py-2 text-xs border border-slate-300 rounded-xl focus:outline-none focus:border-red-700">
            </div>
            <button type="submit" class="px-3.5 py-2 bg-slate-900 hover:bg-red-700 text-white text-xs font-bold rounded-xl cursor-pointer">
                Filtrer
            </button>
        </form>
    </div>

    <!-- Grille des produits -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @forelse($products as $product)
            <article class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-2xs hover:shadow-md transition flex flex-col justify-between group">
                <div>
                    <a href="{{ route('magazine.shop.show', $product->slug) }}" class="relative h-64 bg-slate-100 block overflow-hidden">
                        @if($product->cover_url)
                            <img src="{{ $product->cover_url }}" alt="{{ $product->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                        @endif
                        <div class="absolute top-3 left-3 flex flex-wrap gap-1.5">
                            <span class="px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wider bg-slate-950/90 text-white rounded-md backdrop-blur-xs">
                                {{ $product->typeLabel() }}
                            </span>
                            @if($product->issue_number)
                                <span class="px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wider bg-red-700 text-white rounded-md">
                                    {{ $product->issue_number }}
                                </span>
                            @endif
                        </div>
                        @if($product->is_digital)
                            <span class="absolute bottom-3 right-3 px-2.5 py-1 text-[10px] font-bold bg-emerald-600 text-white rounded-md inline-flex items-center gap-1">
                                <i data-lucide="download" class="w-3 h-3"></i>
                                <span>Papier + PDF HD</span>
                            </span>
                        @endif
                    </a>

                    <div class="p-6 space-y-3">
                        <h2 class="text-lg font-extrabold text-slate-900 group-hover:text-red-700 transition leading-snug">
                            <a href="{{ route('magazine.shop.show', $product->slug) }}">{{ $product->title }}</a>
                        </h2>
                        @if($product->subtitle)
                            <p class="text-xs font-semibold text-slate-500">{{ $product->subtitle }}</p>
                        @endif
                        <p class="text-xs text-slate-600 line-clamp-3 leading-relaxed">
                            {{ $product->excerpt }}
                        </p>
                    </div>
                </div>

                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                    <div>
                        <span class="text-lg font-black text-slate-950">{{ $product->formattedPrice() }}</span>
                        @if($product->formattedComparePrice())
                            <span class="ml-1.5 text-xs text-slate-400 line-through">{{ $product->formattedComparePrice() }}</span>
                        @endif
                    </div>

                    <a href="{{ route('magazine.shop.show', $product->slug) }}" class="px-4 py-2 bg-red-700 hover:bg-red-800 text-white text-xs font-bold uppercase tracking-wider rounded-xl inline-flex items-center gap-1.5 transition">
                        <i data-lucide="shopping-cart" class="w-3.5 h-3.5"></i>
                        <span>Commander</span>
                    </a>
                </div>
            </article>
        @empty
            <div class="col-span-full bg-white rounded-2xl border border-slate-200 p-12 text-center text-sm text-slate-500">
                Aucun ouvrage ou magazine trouvé pour ce filtre.
            </div>
        @endforelse
    </div>

    <div>
        {{ $products->links() }}
    </div>

</div>
@endsection
