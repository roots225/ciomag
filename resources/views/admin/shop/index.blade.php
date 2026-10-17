@extends('layouts.admin')

@section('title', 'Boutique — Magazines & Livres')
@section('header_title', 'Boutique : Magazines & Livres')

@section('top_actions')
    <div class="flex items-center gap-2.5">
        <a href="{{ route('admin.orders.index') }}" class="px-3.5 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 text-xs font-bold uppercase tracking-wider rounded-xl inline-flex items-center gap-1.5 shadow-2xs">
            <i data-lucide="receipt" class="w-4 h-4 text-slate-500"></i>
            <span>Commandes ({{ $ordersCount ?? 0 }})</span>
        </a>
        <a href="{{ route('admin.shop.create') }}" class="px-4 py-2 bg-red-700 hover:bg-red-800 text-white text-xs font-bold uppercase tracking-wider rounded-xl inline-flex items-center gap-1.5 shadow-xs">
            <i data-lucide="plus-circle" class="w-4 h-4"></i>
            <span>Nouveau Magazine / Livre</span>
        </a>
    </div>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Barre de filtres et d'actions -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 flex flex-wrap items-center justify-between gap-4 shadow-2xs">
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.shop.index') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold uppercase {{ !$type ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Tous les produits
            </a>
            <a href="{{ route('admin.shop.index', ['type' => 'magazine']) }}" class="px-3.5 py-2 rounded-xl text-xs font-bold uppercase {{ $type === 'magazine' ? 'bg-red-700 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Magazines
            </a>
            <a href="{{ route('admin.shop.index', ['type' => 'book']) }}" class="px-3.5 py-2 rounded-xl text-xs font-bold uppercase {{ $type === 'book' ? 'bg-red-700 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Livres & Ouvrages
            </a>
            <a href="{{ route('admin.shop.index', ['type' => 'report']) }}" class="px-3.5 py-2 rounded-xl text-xs font-bold uppercase {{ $type === 'report' ? 'bg-red-700 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Études & Rapports
            </a>
        </div>

        <a href="{{ route('admin.shop.create') }}" class="px-4 py-2.5 bg-red-700 hover:bg-red-800 text-white text-xs font-bold uppercase rounded-xl inline-flex items-center gap-1.5">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Nouveau Magazine / Livre</span>
        </a>
    </div>

    <!-- Catalogue Produits -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-2xs">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-sm font-black uppercase tracking-wider text-slate-900 flex items-center gap-2">
                <i data-lucide="book-open" class="w-4 h-4 text-red-700"></i>
                <span>Catalogue Magazines & Ouvrages ({{ $products->total() }})</span>
            </h2>
        </div>

        <table class="w-full text-left border-collapse text-sm">
            <thead>
                <tr class="border-b border-slate-200 bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-500">
                    <th class="py-3.5 px-5">Ouvrage / Magazine</th>
                    <th class="py-3.5 px-4">Type & Numéro</th>
                    <th class="py-3.5 px-4">Prix</th>
                    <th class="py-3.5 px-4">Stock & Format</th>
                    <th class="py-3.5 px-5 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($products as $product)
                    <tr class="hover:bg-slate-50/80">
                        <td class="py-4 px-5 flex items-center gap-3.5">
                            @if($product->cover_url)
                                <img src="{{ $product->cover_url }}" alt="{{ $product->title }}" class="w-11 h-14 rounded-lg object-cover border border-slate-200 shrink-0">
                            @endif
                            <div>
                                <a href="{{ route('admin.shop.edit', $product) }}" class="font-bold text-slate-900 hover:text-red-700">
                                    {{ $product->title }}
                                </a>
                                <div class="text-xs text-slate-400 font-mono">/shop/{{ $product->slug }}</div>
                            </div>
                        </td>
                        <td class="py-4 px-4 text-xs">
                            <span class="px-2 py-0.5 font-bold uppercase bg-slate-100 text-slate-800 rounded-xs">{{ $product->typeLabel() }}</span>
                            @if($product->issue_number)
                                <span class="block text-slate-500 mt-1 font-semibold">{{ $product->issue_number }}</span>
                            @endif
                        </td>
                        <td class="py-4 px-4 font-extrabold text-slate-900">
                            {{ $product->formattedPrice() }}
                        </td>
                        <td class="py-4 px-4 text-xs text-slate-600">
                            <div>Stock : <strong>{{ $product->stock }}</strong></div>
                            <div class="text-emerald-600 font-semibold">{{ $product->is_digital ? 'PDF Numérique inclus' : 'Papier uniquement' }}</div>
                        </td>
                        <td class="py-4 px-5 text-right space-x-1.5">
                            <a href="{{ route('magazine.shop.show', $product->slug) }}" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900">
                                <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                            </a>
                            <a href="{{ route('admin.shop.edit', $product) }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-semibold rounded-lg">
                                <i data-lucide="pen-square" class="w-3.5 h-3.5"></i>
                                <span>Éditer</span>
                            </a>
                            <form action="{{ route('admin.shop.destroy', $product) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer ce produit ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1.5 text-rose-600 hover:bg-rose-50 text-xs font-semibold rounded-lg cursor-pointer">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-slate-400 text-xs">
                            Aucun magazine ou ouvrage trouvé dans cette catégorie.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $products->links() }}
    </div>

</div>
@endsection
