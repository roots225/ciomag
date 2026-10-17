@extends('layouts.admin')

@section('title', 'Boutique — Gestion des Commandes')
@section('header_title', 'Boutique : Commandes Clients')

@section('top_actions')
    <a href="{{ route('admin.shop.index') }}" class="btn btn-sm btn-secondary">
        <i data-lucide="book-open" class="w-4 h-4 text-slate-500"></i>
        <span>Catalogue Magazines & Livres</span>
    </a>
@endsection

@section('content')
@php
    $orderStatusOptions = [
        'pending' => 'En attente',
        'paid' => 'Payée (Confirmée)',
        'shipped' => 'Expédiée',
        'completed' => 'Terminée / Livrée',
        'cancelled' => 'Annulée',
    ];
@endphp

<div x-data="{ activeOrderId: null }" class="space-y-6">

    <!-- KPIs Commandes -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="admin-card p-4 flex items-center justify-between">
            <div>
                <span class="text-xs font-medium text-slate-500">Total Commandes</span>
                <div class="text-xl font-bold text-slate-900 mt-0.5">{{ $stats['total'] }}</div>
                <span class="text-[11px] text-slate-400">Toutes les commandes</span>
            </div>
            <div class="w-10 h-10 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center">
                <i data-lucide="receipt" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="admin-card p-4 flex items-center justify-between">
            <div>
                <span class="text-xs font-medium text-slate-500">Commandes Payées</span>
                <div class="text-xl font-bold text-emerald-600 mt-0.5">{{ $stats['paid'] }}</div>
                <span class="text-[11px] text-emerald-600 font-medium">Validées / Livrées</span>
            </div>
            <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <i data-lucide="check-circle-2" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="admin-card p-4 flex items-center justify-between">
            <div>
                <span class="text-xs font-medium text-slate-500">En attente</span>
                <div class="text-xl font-bold text-amber-600 mt-0.5">{{ $stats['pending'] }}</div>
                <span class="text-[11px] text-amber-600 font-medium">À traiter</span>
            </div>
            <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                <i data-lucide="clock" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="admin-card p-4 flex items-center justify-between">
            <div>
                <span class="text-xs font-medium text-slate-500">Chiffre d'Affaires</span>
                <div class="text-xl font-bold text-red-700 mt-0.5">
                    {{ number_format((float) $stats['revenue'], 2, ',', ' ') }} €
                </div>
                <span class="text-[11px] text-slate-400">Ventes boutique</span>
            </div>
            <div class="w-10 h-10 rounded-lg bg-red-50 text-red-700 flex items-center justify-center">
                <i data-lucide="banknote" class="w-5 h-5"></i>
            </div>
        </div>
    </div>

    <!-- Barre de Filtres et Recherche -->
    <div class="admin-card p-4 flex flex-col lg:flex-row lg:items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-1.5">
            <a href="{{ route('admin.orders.index') }}" class="px-3 py-1.5 rounded-lg text-xs font-medium {{ !$status ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Toutes
            </a>
            <a href="{{ route('admin.orders.index', ['status' => 'paid']) }}" class="px-3 py-1.5 rounded-lg text-xs font-medium {{ $status === 'paid' ? 'bg-emerald-700 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Payées
            </a>
            <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="px-3 py-1.5 rounded-lg text-xs font-medium {{ $status === 'pending' ? 'bg-amber-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                En attente
            </a>
            <a href="{{ route('admin.orders.index', ['status' => 'shipped']) }}" class="px-3 py-1.5 rounded-lg text-xs font-medium {{ $status === 'shipped' ? 'bg-blue-700 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Expédiées
            </a>
            <a href="{{ route('admin.orders.index', ['status' => 'completed']) }}" class="px-3 py-1.5 rounded-lg text-xs font-medium {{ $status === 'completed' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Terminées
            </a>
        </div>

        <form method="GET" action="{{ route('admin.orders.index') }}" class="flex items-center gap-2">
            @if($status)
                <input type="hidden" name="status" value="{{ $status }}">
            @endif
            <div class="relative w-60">
                <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                <input type="text" name="q" value="{{ $search ?? '' }}" placeholder="N° commande, client..."
                       class="w-full pl-8 pr-3 py-1.5 text-xs border border-slate-200 rounded-lg focus:outline-none focus:border-slate-800">
            </div>
            <button type="submit" class="btn btn-sm btn-dark">
                <i data-lucide="search" class="w-3.5 h-3.5"></i>
                <span>Rechercher</span>
            </button>
        </form>
    </div>

    <!-- Tableau des Commandes -->
    <div class="admin-table-container">
        <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-slate-900 flex items-center gap-2">
                <i data-lucide="receipt" class="w-4 h-4 text-red-700"></i>
                <span>Liste des Commandes ({{ $orders->total() }})</span>
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>N° Commande</th>
                        <th>Client & Organisation</th>
                        <th>Articles</th>
                        <th>Montant & Paiement</th>
                        <th>Statut</th>
                        <th>Date</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($orders as $order)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="font-mono font-semibold text-xs text-slate-900">
                                {{ $order->order_number }}
                            </td>
                            <td class="text-xs">
                                <div class="font-semibold text-slate-900">{{ $order->customer_name }}</div>
                                <div class="text-slate-500">{{ $order->customer_email }}</div>
                                @if($order->customer_company)
                                    <div class="text-[11px] font-medium text-slate-400">{{ $order->customer_company }}</div>
                                @endif
                            </td>
                            <td class="text-xs text-slate-700">
                                @php $items = $order->items ?? []; @endphp
                                <span class="font-semibold">{{ count($items) }} article(s)</span>
                                @if(!empty($items[0]['title']))
                                    <div class="text-slate-500 truncate max-w-[200px]">{{ $items[0]['title'] }}</div>
                                @endif
                            </td>
                            <td class="text-xs">
                                <div class="font-bold text-red-700 text-sm">{{ $order->formattedTotal() }}</div>
                                <div class="text-[11px] text-slate-400 uppercase font-medium">{{ $order->payment_method ?? 'Visa' }}</div>
                            </td>
                            <td>
                                @php
                                    $badgeClass = match($order->status) {
                                        'paid', 'completed' => 'badge-success',
                                        'shipped' => 'badge-info',
                                        'pending' => 'badge-warning',
                                        'cancelled' => 'badge-danger',
                                        default => 'badge-neutral',
                                    };
                                @endphp
                                <span class="admin-badge {{ $badgeClass }}">
                                    {{ $orderStatusOptions[$order->status] ?? $order->status }}
                                </span>
                            </td>
                            <td class="text-xs text-slate-500">
                                {{ $order->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="text-right">
                                <div class="inline-flex items-center justify-end gap-1">
                                    <button type="button"
                                            @click="activeOrderId = '{{ $order->id }}'"
                                            class="btn btn-sm btn-secondary">
                                        <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                        <span>Gérer</span>
                                    </button>
                                    <form action="{{ route('admin.orders.destroy', $order) }}" method="POST" onsubmit="return confirm('Supprimer cette commande ?')">
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
                            <td colspan="7" class="py-10 text-center text-slate-400 text-xs">
                                Aucune commande enregistrée pour ces critères.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>
        {{ $orders->links() }}
    </div>

    <!-- Modale de détail & statut de commande -->
    @foreach($orders as $order)
        <div x-show="activeOrderId === '{{ $order->id }}'"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/40 backdrop-blur-xs"
             @keydown.escape.window="activeOrderId = null">
            <div @click.outside="activeOrderId = null"
                 class="bg-white rounded-xl border border-slate-200 shadow-xl w-full max-w-lg overflow-hidden">
                <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900">
                            Commande {{ $order->order_number }}
                        </h3>
                        <p class="text-xs text-slate-500">Passée le {{ $order->created_at->format('d/m/Y à H:i') }}</p>
                    </div>
                    <button type="button" @click="activeOrderId = null" class="p-1 text-slate-400 hover:text-slate-600 rounded cursor-pointer">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <div class="p-5 space-y-4">
                    <div class="grid grid-cols-2 gap-4 bg-slate-50 rounded-lg p-3.5 text-xs">
                        <div>
                            <span class="font-semibold text-slate-400 uppercase text-[10px] block mb-1">Client</span>
                            <div class="font-semibold text-slate-900">{{ $order->customer_name }}</div>
                            <div class="text-slate-600">{{ $order->customer_email }}</div>
                            @if($order->customer_phone)
                                <div class="text-slate-500">{{ $order->customer_phone }}</div>
                            @endif
                        </div>
                        <div>
                            <span class="font-semibold text-slate-400 uppercase text-[10px] block mb-1">Livraison / Société</span>
                            <div class="font-medium text-slate-800">{{ $order->customer_company ?: 'Particulier' }}</div>
                            <div class="text-slate-600 mt-0.5">{{ $order->shipping_address ?: 'Livraison numérique (Email)' }}</div>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-xs font-semibold text-slate-700 mb-2">Articles commandés</h4>
                        <div class="divide-y divide-slate-100 border border-slate-200 rounded-lg overflow-hidden">
                            @foreach($order->items ?? [] as $item)
                                <div class="p-3 flex items-center justify-between text-xs">
                                    <div>
                                        <span class="font-medium text-slate-900">{{ $item['title'] ?? 'Ouvrage CIOMAG' }}</span>
                                        <span class="text-slate-400 ml-1">× {{ $item['quantity'] ?? 1 }}</span>
                                    </div>
                                    <span class="font-semibold text-slate-900">{{ number_format((float) ($item['price'] ?? $order->total_amount), 2, ',', ' ') }} €</span>
                                </div>
                            @endforeach
                            <div class="p-3 bg-slate-50 flex items-center justify-between text-xs font-bold">
                                <span>Total TTC</span>
                                <span class="text-red-700 text-sm">{{ $order->formattedTotal() }}</span>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('admin.orders.status', $order) }}" method="POST" class="space-y-4 pt-3 border-t border-slate-100">
                        @csrf
                        @method('PATCH')
                        <div>
                            <label class="admin-label">Mettre à jour le statut de la commande</label>
                            <x-ui.select name="status" :options="$orderStatusOptions" :value="$order->status" :searchable="false" />
                        </div>

                        <div class="flex items-center justify-end gap-2">
                            <button type="button" @click="activeOrderId = null" class="btn btn-sm btn-ghost">
                                Fermer
                            </button>
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i data-lucide="save" class="w-4 h-4"></i>
                                <span>Enregistrer le statut</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

</div>
@endsection
