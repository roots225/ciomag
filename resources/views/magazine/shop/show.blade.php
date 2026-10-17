@extends('layouts.magazine')

@section('meta_title', $product->title . ' — Boutique CIOMAG')

@section('content')
@php
    $paymentOptions = [
        'card' => 'Carte Bancaire (Visa / Mastercard)',
        'mobile_money' => 'Mobile Money (Orange / Wave / MTN)',
        'bank_transfer' => 'Virement Entreprise / Bon de Commande',
    ];
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-12">

    <div class="text-xs text-slate-500 flex items-center gap-2">
        <a href="{{ route('magazine.shop.index') }}" class="text-red-700 font-bold hover:underline inline-flex items-center gap-1">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
            <span>Retour à la boutique</span>
        </a>
        <span>/</span>
        <span>{{ $product->typeLabel() }}</span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start">
        <!-- Visuel & Présentation détaillée (7 colonnes) -->
        <div class="lg:col-span-7 space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden p-6 sm:p-8 shadow-2xs grid grid-cols-1 sm:grid-cols-12 gap-8 items-start">
                <div class="sm:col-span-5">
                    @if($product->cover_url)
                        <img src="{{ $product->cover_url }}" alt="{{ $product->title }}" class="w-full rounded-xl shadow-md border border-slate-200 object-cover">
                    @endif
                </div>
                <div class="sm:col-span-7 space-y-4">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="px-2.5 py-1 text-[11px] font-bold uppercase bg-red-700 text-white rounded-md">
                            {{ $product->typeLabel() }}
                        </span>
                        @if($product->issue_number)
                            <span class="px-2.5 py-1 text-[11px] font-bold uppercase bg-slate-900 text-amber-400 rounded-md">
                                {{ $product->issue_number }}
                            </span>
                        @endif
                    </div>

                    <h1 class="text-2xl sm:text-3xl font-black text-slate-950 leading-tight">
                        {{ $product->title }}
                    </h1>

                    @if($product->subtitle)
                        <p class="text-sm font-semibold text-slate-600">{{ $product->subtitle }}</p>
                    @endif

                    <div class="pt-2 flex items-baseline gap-3">
                        <span class="text-3xl font-black text-red-700">{{ $product->formattedPrice() }}</span>
                        @if($product->formattedComparePrice())
                            <span class="text-sm text-slate-400 line-through">{{ $product->formattedComparePrice() }}</span>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-3 pt-3 border-t border-slate-100 text-xs text-slate-600">
                        <div class="flex items-center gap-2">
                            <i data-lucide="file-text" class="w-4 h-4 text-red-700"></i>
                            <span><strong>{{ $product->pages_count ?? 96 }}</strong> pages</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600"></i>
                            <span>En stock ({{ $product->stock }} ex.)</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i data-lucide="download" class="w-4 h-4 text-red-700"></i>
                            <span>{{ $product->is_digital ? 'Édition PDF HD incluse' : 'Édition Papier Brochée' }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i data-lucide="user-pen" class="w-4 h-4 text-slate-500"></i>
                            <span>{{ $product->author_name }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-2xs space-y-4">
                <h2 class="text-base font-black uppercase tracking-wider text-slate-900 border-b border-slate-100 pb-3 flex items-center gap-2">
                    <i data-lucide="list-checks" class="w-4 h-4 text-red-700"></i>
                    <span>Sommaire & Présentation de l'édition</span>
                </h2>
                @if($product->excerpt)
                    <p class="text-sm font-semibold text-slate-700 leading-relaxed">{{ $product->excerpt }}</p>
                @endif
                <div class="article-content text-sm text-slate-600">
                    {!! $product->description !!}
                </div>
            </div>
        </div>

        <!-- Formulaire de Commande Directe (5 colonnes) -->
        <div class="lg:col-span-5">
            <div class="bg-white rounded-2xl border-2 border-slate-900 p-6 sm:p-8 shadow-lg space-y-5 sticky top-24">
                <div class="border-b border-slate-100 pb-4">
                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-red-700">Commande Express Sécurisée</span>
                    <h2 class="text-xl font-black text-slate-950 mt-0.5">Commander cet exemplaire</h2>
                </div>

                <form action="{{ route('magazine.shop.checkout', $product->slug) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nom & Prénom *</label>
                        <input type="text" name="customer_name" value="{{ old('customer_name', auth()->user()?->display_name) }}" required class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Email professionnel *</label>
                            <input type="email" name="customer_email" value="{{ old('customer_email', auth()->user()?->email) }}" required class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Téléphone</label>
                            <input type="text" name="customer_phone" value="{{ old('customer_phone') }}" placeholder="+225 / +33..." class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 mb-1">Entreprise / Organisation</label>
                            <input type="text" name="customer_company" value="{{ old('customer_company') }}" placeholder="Ex: Direction des SI" class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Quantité *</label>
                            <input type="number" name="quantity" value="1" min="1" max="20" required class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Mode de règlement *</label>
                        <x-ui.select
                            name="payment_method"
                            :options="$paymentOptions"
                            value="card"
                            :searchable="false"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Adresse de livraison (si édition papier)</label>
                        <textarea name="shipping_address" rows="2" placeholder="Rue, Ville, Pays..." class="w-full px-3.5 py-2 text-sm border border-slate-300 rounded-xl">{{ old('shipping_address') }}</textarea>
                    </div>

                    <button type="submit" class="w-full py-3.5 bg-red-700 hover:bg-red-800 text-white text-sm font-black uppercase tracking-wider rounded-xl inline-flex items-center justify-center gap-2 shadow-md transition cursor-pointer">
                        <i data-lucide="shield-check" class="w-4 h-4"></i>
                        <span>Valider la commande ({{ $product->formattedPrice() }})</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
