@extends('layouts.admin')

@section('title', 'Boutique — Gestion des Commandes')
@section('header_title', 'Boutique : Commandes Clients')

@section('top_actions')
    <a href="{{ route('admin.shop.index') }}" class="px-3.5 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 text-xs font-bold uppercase tracking-wider rounded-xl inline-flex items-center gap-1.5 shadow-2xs">
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
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-2xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Commandes</span>
                <div class="text-2xl font-black text-slate-900 mt-1">{{ $stats['total'] }}</div>
                <span class="text-[11px] text-slate-500">Toutes les commandes</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center">
                <i data-lucide="receipt" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-2xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Commandes Payées</span>
                <div class="text-2xl font-black text-emerald-600 mt-1">{{ $stats['paid'] }}</div>
                <span class="text-[11px] text-emerald-600 font-semibold">Validées / Livrées</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <i data-lucide="check-circle-2" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-2xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">En attente</span>
                <div class="text-2xl font-black text-amber-600 mt-1">{{ $stats['pending'] }}</div>
                <span class="text-[11px] text-amber-600 font-semibold">À traiter</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <i data-lucide="clock" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-2xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Chiffre d'Affaires</span>
                <div class="text-2xl font-black text-red-700 mt-1">
                    {{ number_format((float) $stats['revenue'], 2, ',', ' ') }} €
                </div>
                <span class="text-[11px] text-slate-500">Ventes magazines & livres</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-red-50 text-red-700 flex items-center justify-center">
                <i data-lucide="banknote" class="w-5 h-5"></i>
            </div>
        </div>
    </div>

    <!-- Barre de Filtres et Recherche -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 flex flex-col lg:flex-row lg:items-center justify-between gap-4 shadow-2xs">
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.orders.index') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold uppercase {{ !$status ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Toutes
            </a>
            <a href="{{ route('admin.orders.index', ['status' => 'paid']) }}" class="px-3.5 py-2 rounded-xl text-xs font-bold uppercase {{ $status === 'paid' ? 'bg-emerald-700 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Payées
            </a>
            <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="px-3.5 py-2 rounded-xl text-xs font-bold uppercase {{ $status === 'pending' ? 'bg-amber-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                En attente
            </a>
            <a href="{{ route('admin.orders.index', ['status' => 'shipped']) }}" class="px-3.5 py-2 rounded-xl text-xs font-bold uppercase {{ $status === 'shipped' ? 'bg-blue-700 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Expédiées
            </a>
            <a href="{{ route('admin.orders.index', ['status' => 'completed']) }}" class="px-3.5 py-2 rounded-xl text-xs font-bold uppercase {{ $status === 'completed' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Terminées
            </a>
        </div>

        <form method="GET" action="{{ route('admin.orders.index') }}" class="flex items-center gap-2">
            @if($status)
                <input type="hidden" name="status" value="{{ $status }}">
            @endif
            <div class="relative w-64">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                <input type="text" name="q" value="{{ $search ?? '' }}" placeholder="N° commande, client, email..."
                       class="w-full pl-9 pr-3 py-2 text-xs border border-slate-200 rounded-xl focus:outline-none focus:border-slate-900">
            </div>
            <button type="submit" class="px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl cursor-pointer inline-flex items-center gap-1.5">
                <i data-lucide="search" class="w-3.5 h-3.5"></i>
                <span>Rechercher</span>
            </button>
        </form>
    </div>

    <!-- Tableau des Commandes -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-2xs">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-sm font-black uppercase tracking-wider text-slate-900 flex items-center gap-2">
                <i data-lucide="receipt" class="w-4 h-4 text-red-700"></i>
                <span>Liste des Commandes ({{ $orders->total() }})</span>
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-500">
                        <th class="py-3.5 px-5">N° Commande</th>
                        <th class="py-3.5 px-4">Client & Organisation</th>
                        <th class="py-3.5 px-4">Articles</th>
                        <th class="py-3.5 px-4">Montant & Paiement</th>
                        <th class="py-3.5 px-4">Statut</th>
                        <th class="py-3.5 px-4">Date</th>
                        <th class="py-3.5 px-5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($orders as $order)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-4 px-5 font-mono font-bold text-xs text-slate-900">
                                {{ $order->order_number }}
                            </td>
                            <td class="py-4 px-4 text-xs">
                                <div class="font-bold text-slate-900">{{ $order->customer_name }}</div>
                                <div class="text-slate-500">{{ $order->customer_email }}</div>
                                @if($order->customer_company)
                                    <div class="text-[11px] font-semibold text-slate-400">{{ $order->customer_company }}</div>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-xs text-slate-700">
                                @php $items = $order->items ?? []; @endphp
                                <span class="font-bold">{{ count($items) }} article(s)</span>
                                @if(!empty($items[0]['title']))
                                    <div class="text-slate-500 truncate max-w-[200px]">{{ $items[0]['title'] }}</div>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-xs">
                                <div class="font-extrabold text-red-700 text-sm">{{ $order->formattedTotal() }}</div>
                                <div class="text-[11px] text-slate-400 uppercase font-semibold">{{ $order->payment_method ?? 'Visa' }}</div>
                            </td>
                            <td class="py-4 px-4">
                                @php
                                    $statusBadge = match($order->status) {
                                        'paid', 'completed' => 'bg-emerald-100 text-emerald-800',
                                        'shipped' => 'bg-blue-100 text-blue-800',
                                        'pending' => 'bg-amber-100 text-amber-800',
                                        'cancelled' => 'bg-rose-100 text-rose-800',
                                        default => 'bg-slate-100 text-slate-700',
                                    };
                                @endphp
                                <span class="px-2.5 py-1 text-[10px] font-bold uppercase rounded-md {{ $statusBadge }}">
                                    {{ $orderStatusOptions[$order->status] ?? $order->status }}
                                </span>
                            </td>
                            <td class="py-4 px-4 text-xs text-slate-500">
                                {{ $order->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="py-4 px-5 text-right">
                                <div class="inline-flex items-center justify-end gap-1.5">
                                    <button type="button"
                                            @click="activeOrderId = '{{ $order->id }}'"
                                            class="px-3 py-1.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-lg inline-flex items-center gap-1.5 cursor-pointer">
                                        <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                        <span>Gérer</span>
                                    </button>
                                    <form action="{{ route('admin.orders.destroy', $order) }}" method="POST" onsubmit="return confirm('Supprimer cette commande ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-rose-600 hover:bg-rose-50 rounded-lg cursor-pointer" title="Supprimer">
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

    <!-- Modales de détail & mise à jour de statut des commandes -->
    @foreach($orders as $order)
        <div x-show="activeOrderId === '{{ $order->id }}'"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs"
             @keydown.escape.window="activeOrderId = null">
            <div @click.outside="activeOrderId = null"
                 class="bg-white rounded-2xl border border-slate-200 shadow-2xl w-full max-w-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                    <div>
                        <h3 class="text-sm font-black uppercase tracking-wider text-slate-900">
                            Commande {{ $order->order_number }}
                        </h3>
                        <p class="text-xs text-slate-500">Passée le {{ $order->created_at->format('d/m/Y à H:i') }}</p>
                    </div>
                    <button type="button" @click="activeOrderId = null" class="p-1.5 text-slate-400 hover:text-slate-700 rounded-lg cursor-pointer">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <div class="p-6 space-y-5">
                    <div class="grid grid-cols-2 gap-4 bg-slate-50 rounded-xl p-4 text-xs">
                        <div>
                            <span class="font-bold text-slate-400 uppercase block mb-1">Client</span>
                            <div class="font-bold text-slate-900">{{ $order->customer_name }}</div>
                            <div class="text-slate-600">{{ $order->customer_email }}</div>
                            @if($order->customer_phone)
                                <div class="text-slate-500">{{ $order->customer_phone }}</div>
                            @endif
                        </div>
                        <div>
                            <span class="font-bold text-slate-400 uppercase block mb-1">Livraison / Société</span>
                            <div class="font-semibold text-slate-800">{{ $order->customer_company ?: 'Particulier' }}</div>
                            <div class="text-slate-600 mt-0.5">{{ $order->shipping_address ?: 'Livraison numérique (Email)' }}</div>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Articles commandés</h4>
                        <div class="divide-y divide-slate-100 border border-slate-200 rounded-xl overflow-hidden">
                            @foreach($order->items ?? [] as $item)
                                <div class="p-3 flex items-center justify-between text-xs">
                                    <div>
                                        <span class="font-bold text-slate-900">{{ $item['title'] ?? 'Ouvrage CIOMAG' }}</span>
                                        <span class="text-slate-400 ml-1">× {{ $item['quantity'] ?? 1 }}</span>
                                    </div>
                                    <span class="font-bold text-slate-900">{{ number_format((float) ($item['price'] ?? $order->total_amount), 2, ',', ' ') }} €</span>
                                </div>
                            @endforeach
                            <div class="p-3 bg-slate-50 flex items-center justify-between text-xs font-extrabold">
                                <span>Total TTC</span>
                                <span class="text-red-700 text-sm">{{ $order->formattedTotal() }}</span>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('admin.orders.status', $order) }}" method="POST" class="space-y-4 pt-2 border-t border-slate-100">
                        @csrf
                        @method('PATCH')
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Mettre à jour le statut de la commande</label>
                            <x-ui.select name="status" :options="$orderStatusOptions" :value="$order->status" :searchable="false" />
                        </div>

                        <div class="flex items-center justify-end gap-2">
                            <button type="button" @click="activeOrderId = null" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl cursor-pointer">
                                Fermer
                            </button>
                            <button type="submit" class="px-5 py-2.5 bg-slate-900 hover:bg-red-700 text-white text-xs font-bold uppercase tracking-wider rounded-xl inline-flex items-center gap-1.5 cursor-pointer">
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
