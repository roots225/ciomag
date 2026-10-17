@extends('layouts.admin')

@section('title', 'Méthodes de Paiement — Carte Visa (Stripe / Braintree)')
@section('header_title', 'Méthodes de Paiement')

@section('top_actions')
    <form action="{{ route('admin.payment-methods.test') }}" method="POST" class="inline">
        @csrf
        <input type="hidden" name="provider" value="{{ $config['visa']['provider'] ?? 'stripe' }}">
        <button type="submit" class="btn btn-sm btn-secondary">
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
        'sandbox' => 'Sandbox / Test (Environnement de test)',
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

<div x-data="{ selectedGateway: '{{ $activeProvider }}' }" class="space-y-6">

    <!-- Bannière Récapitulative Carte Visa (Style SaaS Sobre & Moderne) -->
    <div class="admin-card p-5 sm:p-6 bg-white border border-slate-200 shadow-2xs">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5">
            <div class="flex items-start sm:items-center gap-4">
                <div class="w-14 h-10 rounded-lg bg-slate-900 text-white font-extrabold italic text-lg tracking-tighter flex items-center justify-center shrink-0 border border-slate-800">
                    VISA
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-base font-semibold text-slate-900">{{ $config['visa']['display_name'] ?? 'Carte Bancaire Visa' }}</h2>
                        <span class="admin-badge {{ $isVisaEnabled ? 'badge-success' : 'badge-danger' }}">
                            {{ $isVisaEnabled ? 'Méthode Active' : 'Désactivée' }}
                        </span>
                        <span class="admin-badge badge-info">
                            Passerelle : {{ strtoupper($activeProvider) }}
                        </span>
                        <span class="admin-badge {{ ($config['visa']['mode'] ?? 'sandbox') === 'live' ? 'badge-danger' : 'badge-warning' }}">
                            Mode {{ strtoupper($config['visa']['mode'] ?? 'sandbox') }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">
                        Encaissement sécurisé par carte Visa pour la Boutique (Magazines & Livres), les Abonnements Premium et les Événements CIOMAG.
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                <div class="px-3 py-1.5 rounded-lg bg-slate-50 border border-slate-200 text-xs">
                    <span class="text-slate-400 block text-[10px] uppercase font-semibold">Sécurité</span>
                    <span class="font-medium text-slate-700 flex items-center gap-1.5 mt-0.5">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-600"></i>
                        {{ ($config['visa']['three_d_secure'] ?? true) ? '3D Secure v2 Actif' : 'Standard' }}
                    </span>
                </div>
                <div class="px-3 py-1.5 rounded-lg bg-slate-50 border border-slate-200 text-xs">
                    <span class="text-slate-400 block text-[10px] uppercase font-semibold">Devise par défaut</span>
                    <span class="font-medium text-slate-800 mt-0.5 block">{{ $config['visa']['currency'] ?? 'EUR' }}</span>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.payment-methods.update') }}" method="POST" class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        @csrf
        @method('PUT')

        <!-- Colonne gauche (5 cols) : Paramètres généraux de la méthode Visa -->
        <div class="lg:col-span-5 space-y-6">
            <div class="admin-card p-5 sm:p-6 space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-semibold text-slate-900 flex items-center gap-2">
                        <i data-lucide="credit-card" class="w-4 h-4 text-red-700"></i>
                        <span>Configuration Générale Visa</span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Paramétrez l'affichage et le processeur de paiement pour les cartes Visa.</p>
                </div>

                <div>
                    <label class="admin-label">Libellé affiché aux clients *</label>
                    <input type="text" name="display_name" required value="{{ $config['visa']['display_name'] ?? 'Carte Bancaire Visa' }}"
                           class="admin-input">
                </div>

                <div @change="if ($event.target.name === 'provider') selectedGateway = $event.target.value">
                    <label class="admin-label">Processeur de paiement Visa actif *</label>
                    <x-ui.select name="provider" :options="$providerOptions" :value="$activeProvider" :searchable="false" />
                    <p class="admin-help-text">Choisissez entre Stripe ou Braintree pour traiter les paiements par carte Visa.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="admin-label">Environnement *</label>
                        <x-ui.select name="mode" :options="$modeOptions" :value="$config['visa']['mode'] ?? 'sandbox'" :searchable="false" />
                    </div>
                    <div>
                        <label class="admin-label">Devise d'encaissement *</label>
                        <x-ui.select name="currency" :options="$currencyOptions" :value="$config['visa']['currency'] ?? 'EUR'" :searchable="false" />
                    </div>
                </div>

                <div>
                    <label class="admin-label">Libellé bancaire (Statement Descriptor)</label>
                    <input type="text" name="statement_descriptor" maxlength="22" value="{{ $config['visa']['statement_descriptor'] ?? 'CIOMAG MEDIA' }}"
                           class="admin-input font-mono text-xs">
                    <p class="admin-help-text">Apparaît sur le relevé bancaire Visa du client (max. 22 caractères).</p>
                </div>

                <div class="space-y-2 pt-3 border-t border-slate-100">
                    <span class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Options & Modules couverts</span>
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
                     :class="selectedGateway === 'stripe' ? 'border-red-600 bg-red-50/20 ring-1 ring-red-600/20' : 'border-slate-200 bg-white hover:bg-slate-50'"
                     class="rounded-xl border p-4 cursor-pointer transition flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-slate-900 text-white font-bold text-xs flex items-center justify-center">
                            S
                        </div>
                        <div>
                            <div class="font-semibold text-sm text-slate-900">Visa via Stripe</div>
                            <div class="text-xs text-slate-500">PaymentIntents & Checkout</div>
                        </div>
                    </div>
                    <span x-show="selectedGateway === 'stripe'" class="admin-badge badge-primary">
                        Actif
                    </span>
                </div>

                <div @click="selectedGateway = 'braintree'"
                     :class="selectedGateway === 'braintree' ? 'border-red-600 bg-red-50/20 ring-1 ring-red-600/20' : 'border-slate-200 bg-white hover:bg-slate-50'"
                     class="rounded-xl border p-4 cursor-pointer transition flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-slate-800 text-white font-bold text-xs flex items-center justify-center">
                            B
                        </div>
                        <div>
                            <div class="font-semibold text-sm text-slate-900">Visa via Braintree</div>
                            <div class="text-xs text-slate-500">Hosted Fields & Drop-in UI</div>
                        </div>
                    </div>
                    <span x-show="selectedGateway === 'braintree'" class="admin-badge badge-primary">
                        Actif
                    </span>
                </div>
            </div>

            <!-- Configuration Passerelle 1 : STRIPE (Visa) -->
            <div class="admin-card p-5 sm:p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2.5">
                        <span class="w-7 h-7 rounded-lg bg-slate-900 text-white font-bold text-xs flex items-center justify-center">S</span>
                        <div>
                            <h3 class="text-sm font-semibold text-slate-900">Paramètres API Stripe (Carte Visa)</h3>
                            <p class="text-xs text-slate-500">Clés API Stripe pour le traitement des cartes Visa.</p>
                        </div>
                    </div>
                    <span class="admin-badge {{ $activeProvider === 'stripe' ? 'badge-success' : 'badge-neutral' }}">
                        {{ $activeProvider === 'stripe' ? 'Passerelle Active' : 'Passerelle Secondaire' }}
                    </span>
                </div>

                <div>
                    <label class="admin-label">Stripe Publishable Key (Clé publique Visa)</label>
                    <input type="text" name="stripe_publishable_key" value="{{ $config['stripe']['publishable_key'] ?? '' }}"
                           placeholder="pk_test_..."
                           class="admin-input font-mono text-xs">
                </div>

                <div>
                    <label class="admin-label">Stripe Secret Key (Clé secrète)</label>
                    <input type="password" name="stripe_secret_key" value="{{ $config['stripe']['secret_key'] ?? '' }}"
                           placeholder="sk_test_..."
                           class="admin-input font-mono text-xs">
                </div>

                <div>
                    <label class="admin-label">Stripe Webhook Signing Secret</label>
                    <input type="text" name="stripe_webhook_secret" value="{{ $config['stripe']['webhook_secret'] ?? '' }}"
                           placeholder="whsec_..."
                           class="admin-input font-mono text-xs">
                    <p class="admin-help-text">
                        URL de Webhook à renseigner sur Stripe : <code class="text-slate-700 font-semibold bg-slate-100 px-1 py-0.5 rounded">{{ url('/webhooks/stripe/visa') }}</code>
                    </p>
                </div>
            </div>

            <!-- Configuration Passerelle 2 : BRAINTREE (Visa) -->
            <div class="admin-card p-5 sm:p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2.5">
                        <span class="w-7 h-7 rounded-lg bg-slate-800 text-white font-bold text-xs flex items-center justify-center">B</span>
                        <div>
                            <h3 class="text-sm font-semibold text-slate-900">Paramètres API Braintree (Carte Visa)</h3>
                            <p class="text-xs text-slate-500">Identifiants Merchant Braintree pour l'encaissement des cartes Visa.</p>
                        </div>
                    </div>
                    <span class="admin-badge {{ $activeProvider === 'braintree' ? 'badge-success' : 'badge-neutral' }}">
                        {{ $activeProvider === 'braintree' ? 'Passerelle Active' : 'Passerelle Secondaire' }}
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="admin-label">Braintree Merchant ID</label>
                        <input type="text" name="braintree_merchant_id" value="{{ $config['braintree']['merchant_id'] ?? '' }}"
                               placeholder="ex: ciomag_bt_merchant"
                               class="admin-input font-mono text-xs">
                    </div>
                    <div>
                        <label class="admin-label">Merchant Account ID (Devise Visa)</label>
                        <input type="text" name="braintree_merchant_account_id" value="{{ $config['braintree']['merchant_account_id'] ?? '' }}"
                               placeholder="ex: ciomag_eur_visa"
                               class="admin-input font-mono text-xs">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="admin-label">Braintree Public Key</label>
                        <input type="text" name="braintree_public_key" value="{{ $config['braintree']['public_key'] ?? '' }}"
                               placeholder="Public Key..."
                               class="admin-input font-mono text-xs">
                    </div>
                    <div>
                        <label class="admin-label">Braintree Private Key</label>
                        <input type="password" name="braintree_private_key" value="{{ $config['braintree']['private_key'] ?? '' }}"
                               placeholder="Private Key..."
                               class="admin-input font-mono text-xs">
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="submit" class="btn btn-md btn-primary">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Enregistrer la configuration Visa</span>
                </button>
            </div>
        </div>
    </form>

</div>
@endsection
