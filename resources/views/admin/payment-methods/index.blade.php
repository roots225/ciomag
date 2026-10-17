@extends('layouts.admin')

@section('title', 'Méthodes de Paiement — Carte Visa (Stripe / Braintree)')
@section('header_title', 'Méthodes de Paiement')

@section('top_actions')
    <form action="{{ route('admin.payment-methods.test') }}" method="POST" class="inline">
        @csrf
        <input type="hidden" name="provider" value="{{ $config['visa']['provider'] ?? 'stripe' }}">
        <button type="submit" class="px-3.5 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 text-xs font-bold uppercase tracking-wider rounded-xl inline-flex items-center gap-2 cursor-pointer shadow-2xs">
            <i data-lucide="plug-zap" class="w-4 h-4 text-emerald-600"></i>
            <span>Tester la passerelle active ({{ strtoupper($config['visa']['provider'] ?? 'STRIPE') }})</span>
        </button>
    </form>
@endsection

@section('content')
@php
    $providerOptions = [
        'stripe' => 'Stripe (Visa Direct / PaymentIntents)',
        'braintree' => 'Braintree — A PayPal Service (Visa Drop-in / Hosted Fields)',
    ];

    $modeOptions = [
        'sandbox' => 'Sandbox / Test (Environnement de développement)',
        'live' => 'Production / Live (Transactions réelles)',
    ];

    $currencyOptions = [
        'EUR' => 'EUR (€) — Euro',
        'USD' => 'USD ($) — Dollar US',
        'XOF' => 'XOF (FCFA) — Franc CFA',
    ];

    $activeProvider = $config['visa']['provider'] ?? 'stripe';
    $isVisaEnabled = (bool) ($config['visa']['enabled'] ?? true);
@endphp

<div x-data="{ selectedGateway: '{{ $activeProvider }}' }" class="space-y-8">

    <!-- Bannière Récapitulative Carte Visa -->
    <div class="bg-linear-to-r from-slate-900 via-slate-900 to-blue-950 rounded-2xl p-6 text-white shadow-lg flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div class="flex items-start sm:items-center gap-4">
            <div class="w-16 h-12 rounded-xl bg-white text-blue-900 font-black italic text-xl tracking-tighter flex items-center justify-center shadow-md shrink-0">
                VISA
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="text-lg font-extrabold tracking-tight">{{ $config['visa']['display_name'] ?? 'Carte Bancaire Visa' }}</h2>
                    <span class="px-2.5 py-0.5 text-[10px] font-bold uppercase rounded-full {{ $isVisaEnabled ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-400/30' : 'bg-rose-500/20 text-rose-300 border border-rose-400/30' }}">
                        {{ $isVisaEnabled ? 'Méthode Active' : 'Désactivée' }}
                    </span>
                    <span class="px-2.5 py-0.5 text-[10px] font-bold uppercase rounded-full bg-blue-500/20 text-blue-200 border border-blue-400/30">
                        Passerelle : {{ strtoupper($activeProvider) }}
                    </span>
                    <span class="px-2.5 py-0.5 text-[10px] font-bold uppercase rounded-full {{ ($config['visa']['mode'] ?? 'sandbox') === 'live' ? 'bg-red-500/30 text-red-200' : 'bg-amber-500/20 text-amber-200' }}">
                        Mode {{ strtoupper($config['visa']['mode'] ?? 'sandbox') }}
                    </span>
                </div>
                <p class="text-xs text-slate-300 mt-1">
                    Encaissement sécurisé par carte Visa pour la Boutique (Magazines & Livres), les Abonnements Premium et les Événements CIOMAG.
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3 shrink-0">
            <div class="px-3.5 py-2 rounded-xl bg-white/5 border border-white/10 text-xs">
                <span class="text-slate-400 block text-[10px] uppercase font-bold">Sécurité</span>
                <span class="font-bold text-white flex items-center gap-1">
                    <i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-400"></i>
                    {{ ($config['visa']['three_d_secure'] ?? true) ? '3D Secure v2 Actif' : 'Standard' }}
                </span>
            </div>
            <div class="px-3.5 py-2 rounded-xl bg-white/5 border border-white/10 text-xs">
                <span class="text-slate-400 block text-[10px] uppercase font-bold">Devise par défaut</span>
                <span class="font-bold text-white">{{ $config['visa']['currency'] ?? 'EUR' }}</span>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.payment-methods.update') }}" method="POST" class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        @csrf
        @method('PUT')

        <!-- Colonne gauche (5 cols) : Paramètres généraux de la méthode Visa -->
        <div class="lg:col-span-5 space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-2xs space-y-5">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-black uppercase tracking-wider text-slate-900 flex items-center gap-2">
                        <i data-lucide="credit-card" class="w-4 h-4 text-red-700"></i>
                        <span>Configuration Générale Visa</span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Paramétrez l'affichage et le processeur de paiement pour les cartes Visa.</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Libellé affiché aux clients *</label>
                    <input type="text" name="display_name" required value="{{ $config['visa']['display_name'] ?? 'Carte Bancaire Visa' }}"
                           class="w-full px-3.5 py-2 text-sm border border-slate-300 rounded-xl">
                </div>

                <div @change="if ($event.target.name === 'provider') selectedGateway = $event.target.value">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Processeur de paiement Visa actif *</label>
                    <x-ui.select name="provider" :options="$providerOptions" :value="$activeProvider" :searchable="false" />
                    <p class="text-[11px] text-slate-400 mt-1">Choisissez entre Stripe ou Braintree pour traiter les paiements par carte Visa.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Environnement *</label>
                        <x-ui.select name="mode" :options="$modeOptions" :value="$config['visa']['mode'] ?? 'sandbox'" :searchable="false" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Devise d'encaissement *</label>
                        <x-ui.select name="currency" :options="$currencyOptions" :value="$config['visa']['currency'] ?? 'EUR'" :searchable="false" />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Libellé bancaire (Statement Descriptor)</label>
                    <input type="text" name="statement_descriptor" maxlength="22" value="{{ $config['visa']['statement_descriptor'] ?? 'CIOMAG MEDIA' }}"
                           class="w-full px-3.5 py-2 text-sm font-mono border border-slate-300 rounded-xl">
                    <p class="text-[11px] text-slate-400 mt-1">Apparaît sur le relevé bancaire Visa du client (max. 22 caractères).</p>
                </div>

                <div class="space-y-2.5 pt-2 border-t border-slate-100">
                    <span class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Options & Modules couverts</span>
                    <x-ui.checkbox name="visa_enabled" value="1" :checked="$isVisaEnabled" label="Activer le paiement par Carte Visa sur la plateforme" />
                    <x-ui.checkbox name="three_d_secure" value="1" :checked="(bool) ($config['visa']['three_d_secure'] ?? true)" label="Exiger l'authentification forte 3D Secure (PSD2 / SCA)" />
                    <x-ui.checkbox name="allow_shop_orders" value="1" :checked="(bool) ($config['visa']['allow_shop_orders'] ?? true)" label="Autoriser pour la Boutique (Magazines & Livres)" />
                    <x-ui.checkbox name="allow_subscriptions" value="1" :checked="(bool) ($config['visa']['allow_subscriptions'] ?? true)" label="Autoriser pour les Abonnements Lecteurs Premium" />
                    <x-ui.checkbox name="allow_events" value="1" :checked="(bool) ($config['visa']['allow_events'] ?? true)" label="Autoriser pour les Billets d'Événements" />
                </div>
            </div>
        </div>

        <!-- Colonne droite (7 cols) : Identifiants API Stripe & Braintree -->
        <div class="lg:col-span-7 space-y-6">

            <!-- Sélecteur visuel rapide Stripe vs Braintree -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div @click="selectedGateway = 'stripe'"
                     :class="selectedGateway === 'stripe' ? 'border-indigo-600 bg-indigo-50/40 ring-2 ring-indigo-600/15' : 'border-slate-200 bg-white hover:bg-slate-50'"
                     class="rounded-2xl border p-4 cursor-pointer transition flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white font-black text-sm flex items-center justify-center">
                            S
                        </div>
                        <div>
                            <div class="font-extrabold text-sm text-slate-900">Visa via Stripe</div>
                            <div class="text-xs text-slate-500">PaymentIntents & Checkout</div>
                        </div>
                    </div>
                    <span x-show="selectedGateway === 'stripe'" class="px-2 py-0.5 text-[10px] font-bold uppercase bg-indigo-600 text-white rounded-md">
                        Sélectionné
                    </span>
                </div>

                <div @click="selectedGateway = 'braintree'"
                     :class="selectedGateway === 'braintree' ? 'border-slate-900 bg-slate-900/5 ring-2 ring-slate-900/15' : 'border-slate-200 bg-white hover:bg-slate-50'"
                     class="rounded-2xl border p-4 cursor-pointer transition flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-slate-900 text-white font-black text-sm flex items-center justify-center">
                            B
                        </div>
                        <div>
                            <div class="font-extrabold text-sm text-slate-900">Visa via Braintree</div>
                            <div class="text-xs text-slate-500">Hosted Fields & Drop-in UI</div>
                        </div>
                    </div>
                    <span x-show="selectedGateway === 'braintree'" class="px-2 py-0.5 text-[10px] font-bold uppercase bg-slate-900 text-white rounded-md">
                        Sélectionné
                    </span>
                </div>
            </div>

            <!-- Configuration Passerelle 1 : STRIPE (Visa) -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-2xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2.5">
                        <span class="w-7 h-7 rounded-lg bg-indigo-600 text-white font-black text-xs flex items-center justify-center">S</span>
                        <div>
                            <h3 class="text-sm font-black uppercase tracking-wider text-slate-900">Paramètres API Stripe (Carte Visa)</h3>
                            <p class="text-xs text-slate-500">Clés API Stripe pour le traitement des cartes Visa.</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 text-[10px] font-bold uppercase rounded-md {{ $activeProvider === 'stripe' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                        {{ $activeProvider === 'stripe' ? 'Passerelle Active' : 'Passerelle Secondaire' }}
                    </span>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Stripe Publishable Key (Clé publique Visa)</label>
                    <input type="text" name="stripe_publishable_key" value="{{ $config['stripe']['publishable_key'] ?? '' }}"
                           placeholder="pk_test_..."
                           class="w-full px-3.5 py-2 text-xs font-mono border border-slate-300 rounded-xl">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Stripe Secret Key (Clé secrète)</label>
                    <input type="password" name="stripe_secret_key" value="{{ $config['stripe']['secret_key'] ?? '' }}"
                           placeholder="sk_test_..."
                           class="w-full px-3.5 py-2 text-xs font-mono border border-slate-300 rounded-xl">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Stripe Webhook Signing Secret</label>
                    <input type="text" name="stripe_webhook_secret" value="{{ $config['stripe']['webhook_secret'] ?? '' }}"
                           placeholder="whsec_..."
                           class="w-full px-3.5 py-2 text-xs font-mono border border-slate-300 rounded-xl">
                    <p class="text-[11px] text-slate-400 mt-1">
                        URL de Webhook à renseigner sur Stripe : <code class="text-slate-700 font-semibold">{{ url('/webhooks/stripe/visa') }}</code>
                    </p>
                </div>
            </div>

            <!-- Configuration Passerelle 2 : BRAINTREE (Visa) -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-2xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2.5">
                        <span class="w-7 h-7 rounded-lg bg-slate-900 text-white font-black text-xs flex items-center justify-center">B</span>
                        <div>
                            <h3 class="text-sm font-black uppercase tracking-wider text-slate-900">Paramètres API Braintree (Carte Visa)</h3>
                            <p class="text-xs text-slate-500">Identifiants Merchant Braintree pour l'encaissement des cartes Visa.</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 text-[10px] font-bold uppercase rounded-md {{ $activeProvider === 'braintree' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                        {{ $activeProvider === 'braintree' ? 'Passerelle Active' : 'Passerelle Secondaire' }}
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Braintree Merchant ID</label>
                        <input type="text" name="braintree_merchant_id" value="{{ $config['braintree']['merchant_id'] ?? '' }}"
                               placeholder="ex: ciomag_bt_merchant"
                               class="w-full px-3.5 py-2 text-xs font-mono border border-slate-300 rounded-xl">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Merchant Account ID (Devise Visa)</label>
                        <input type="text" name="braintree_merchant_account_id" value="{{ $config['braintree']['merchant_account_id'] ?? '' }}"
                               placeholder="ex: ciomag_eur_visa"
                               class="w-full px-3.5 py-2 text-xs font-mono border border-slate-300 rounded-xl">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Braintree Public Key</label>
                        <input type="text" name="braintree_public_key" value="{{ $config['braintree']['public_key'] ?? '' }}"
                               placeholder="Public Key..."
                               class="w-full px-3.5 py-2 text-xs font-mono border border-slate-300 rounded-xl">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Braintree Private Key</label>
                        <input type="password" name="braintree_private_key" value="{{ $config['braintree']['private_key'] ?? '' }}"
                               placeholder="Private Key..."
                               class="w-full px-3.5 py-2 text-xs font-mono border border-slate-300 rounded-xl">
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <button type="submit" class="px-6 py-3 bg-red-700 hover:bg-red-800 text-white text-xs font-bold uppercase tracking-wider rounded-xl shadow-sm inline-flex items-center gap-2 cursor-pointer transition">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Enregistrer la configuration Visa</span>
                </button>
            </div>
        </div>
    </form>

</div>
@endsection
