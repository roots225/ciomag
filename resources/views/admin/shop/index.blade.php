@extends('layouts.admin')

@section('title', 'Boutique — Magazines & Livres')
@section('header_title', 'Boutique : Magazines & Livres')

@section('top_actions')
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-secondary">
            <i data-lucide="receipt" class="w-4 h-4 text-slate-500"></i>
            <span>Commandes ({{ $ordersCount ?? 0 }})</span>
        </a>
        <a href="{{ route('admin.shop.create') }}" class="btn btn-sm btn-primary">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Nouveau Magazine / Livre</span>
        </a>
    </div>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Barre de filtres et d'actions -->
    <div class="admin-card p-4 flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-1.5">
            <a href="{{ route('admin.shop.index') }}" class="px-3 py-1.5 rounded-lg text-xs font-medium {{ !$type ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Tous les produits
            </a>
            <a href="{{ route('admin.shop.index', ['type' => 'magazine']) }}" class="px-3 py-1.5 rounded-lg text-xs font-medium {{ $type === 'magazine' ? 'bg-red-700 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Magazines
            </a>
            <a href="{{ route('admin.shop.index', ['type' => 'book']) }}" class="px-3 py-1.5 rounded-lg text-xs font-medium {{ $type === 'book' ? 'bg-red-700 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Livres & Ouvrages
            </a>
            <a href="{{ route('admin.shop.index', ['type' => 'report']) }}" class="px-3 py-1.5 rounded-lg text-xs font-medium {{ $type === 'report' ? 'bg-red-700 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Études & Rapports
            </a>
        </div>

        <a href="{{ route('admin.shop.create') }}" class="btn btn-sm btn-primary">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Nouveau Magazine / Livre</span>
        </a>
    </div>

    <!-- Catalogue Produits -->
    <div class="admin-table-container">
        <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-slate-900 flex items-center gap-2">
                <i data-lucide="book-open" class="w-4 h-4 text-red-700"></i>
                <span>Catalogue Magazines & Ouvrages ({{ $products->total() }})</span>
            </h2>
        </div>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>Ouvrage / Magazine</th>
                    <th>Type & Numéro</th>
                    <th>Prix</th>
                    <th>Stock & Format</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($products as $product)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="flex items-center gap-3">
                            @if($product->cover_url)
                                <img src="{{ $product->cover_url }}" alt="{{ $product->title }}" class="w-10 h-13 rounded-md object-cover border border-slate-200 shrink-0">
                            @endif
                            <div class="min-w-0">
                                <a href="{{ route('admin.shop.edit', $product) }}" class="font-semibold text-slate-900 hover:text-red-700 transition">
                                    {{ $product->title }}
                                </a>
                                <div class="text-[11px] text-slate-400 font-mono">/shop/{{ $product->slug }}</div>
                            </div>
                        </td>
                        <td class="text-xs">
                            <span class="admin-badge badge-neutral">{{ $product->typeLabel() }}</span>
                            @if($product->issue_number)
                                <span class="block text-slate-500 mt-1 font-medium">{{ $product->issue_number }}</span>
                            @endif
                        </td>
                        <td class="font-bold text-slate-900">
                            {{ $product->formattedPrice() }}
                        </td>
                        <td class="text-xs text-slate-600">
                            <div>Stock : <span class="font-medium text-slate-800">{{ $product->stock }}</span></div>
                            <div class="text-emerald-600 font-medium">{{ $product->is_digital ? 'PDF Numérique inclus' : 'Papier uniquement' }}</div>
                        </td>
                        <td class="text-right">
                            <div class="inline-flex items-center justify-end gap-1">
                                <a href="{{ route('magazine.shop.show', $product->slug) }}" target="_blank" class="p-1.5 text-slate-400 hover:text-slate-700 rounded-lg transition" title="Voir en ligne">
                                    <i data-lucide="external-link" class="w-4 h-4"></i>
                                </a>
                                <a href="{{ route('admin.shop.edit', $product) }}" class="btn btn-sm btn-secondary">
                                    <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                    <span>Éditer</span>
                                </a>
                                <form action="{{ route('admin.shop.destroy', $product) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer ce produit ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-rose-600 hover:bg-rose-50 rounded-lg cursor-pointer transition" title="Supprimer">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </div>
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
