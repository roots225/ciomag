@extends('layouts.magazine')

@section('meta_title', 'Abonnements CIOMAG Premium — Accès Exclusif DSI & Dirigeants IT')

@section('content')
@php
    $paymentMethodOptions = [
        'card' => 'Carte Bancaire (Activation immédiate)',
        'mobile_money' => 'Mobile Money (Orange / Wave / MTN)',
        'corporate_invoice' => 'Facturation Entreprise / Bon de Commande',
    ];
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-14" x-data="{ selectedPlanId: '{{ $plans->firstWhere('is_featured', true)?->id ?? $plans->first()?->id }}' }">

    <!-- En-tête Abonnements Premium -->
    <div class="text-center max-w-3xl mx-auto space-y-4">
        <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full bg-slate-950 text-amber-400 text-xs font-extrabold uppercase tracking-wider">
            <i data-lucide="crown" class="w-3.5 h-3.5"></i>
            <span>Club & Édition Abonnés CIOMAG Premium</span>
        </span>
        <h1 class="text-3xl sm:text-5xl font-black text-slate-950 tracking-tight leading-tight">
            L'information stratégique de référence pour les décideurs IT
        </h1>
        <p class="text-slate-600 text-base leading-relaxed">
            Accédez en illimité à nos enquêtes exclusives, nos benchmarks salaires & budgets IT, nos livres blancs et recevez chaque numéro bimestriel du magazine CIOMAG.
        </p>

        @auth
            @if($activeSub = auth()->user()->activeSubscription())
                <div class="mt-4 inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-sm font-bold">
                    <i data-lucide="badge-check" class="w-5 h-5 text-emerald-600"></i>
                    <span>Vous bénéficiez d'un abonnement actif : <strong>{{ $activeSub->plan?->name }}</strong> (valide jusqu'au {{ $activeSub->expires_at ? $activeSub->expires_at->format('d/m/Y') : 'Illimité' }})</span>
                </div>
            @endif
        @endauth
    </div>

    <!-- Grille des Formules d'Abonnement -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-stretch">
        @foreach($plans as $plan)
            <div
                @click="selectedPlanId = '{{ $plan->id }}'"
                class="rounded-3xl p-8 flex flex-col justify-between transition cursor-pointer border-2 {{ $plan->is_featured ? 'bg-slate-950 text-white border-amber-400 shadow-xl scale-[1.02]' : 'bg-white text-slate-900 border-slate-200 hover:border-slate-400 shadow-2xs' }}"
            >
                <div class="space-y-5">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs font-extrabold uppercase tracking-wider {{ $plan->is_featured ? 'text-amber-400' : 'text-red-700' }}">
                            {{ $plan->badge ?? 'Formule Abonné' }}
                        </span>
                        <span
                            class="w-6 h-6 rounded-full flex items-center justify-center border"
                            :class="selectedPlanId === '{{ $plan->id }}' ? 'bg-amber-400 border-amber-400 text-slate-950' : 'border-slate-400'"
                        >
                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                        </span>
                    </div>

                    <h2 class="text-2xl font-black">{{ $plan->name }}</h2>

                    <div class="flex items-baseline gap-1.5">
                        <span class="text-4xl font-black {{ $plan->is_featured ? 'text-amber-400' : 'text-slate-950' }}">
                            {{ $plan->formattedPrice() }}
                        </span>
                        <span class="text-xs {{ $plan->is_featured ? 'text-slate-400' : 'text-slate-500' }}">
                            / {{ $plan->intervalLabel() }}
                        </span>
                    </div>

                    <p class="text-xs leading-relaxed {{ $plan->is_featured ? 'text-slate-300' : 'text-slate-600' }}">
                        {{ $plan->description }}
                    </p>

                    <ul class="pt-4 border-t {{ $plan->is_featured ? 'border-slate-800' : 'border-slate-100' }} space-y-3 text-xs">
                        @foreach($plan->features ?? [] as $feature)
                            <li class="flex items-start gap-2.5">
                                <i data-lucide="check-circle-2" class="w-4 h-4 shrink-0 mt-0.5 {{ $plan->is_featured ? 'text-amber-400' : 'text-emerald-600' }}"></i>
                                <span>{{ $feature }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="mt-8 pt-4">
                    <a
                        href="#subscribe-form"
                        @click="selectedPlanId = '{{ $plan->id }}'"
                        class="w-full py-3 rounded-xl text-xs font-black uppercase tracking-wider inline-flex items-center justify-center gap-2 transition {{ $plan->is_featured ? 'bg-amber-400 hover:bg-amber-300 text-slate-950' : 'bg-slate-900 hover:bg-red-700 text-white' }}"
                    >
                        <i data-lucide="sparkles" class="w-4 h-4"></i>
                        <span>Choisir cette formule</span>
                    </a>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Formulaire de Souscription Immédiate -->
    <div id="subscribe-form" class="max-w-3xl mx-auto bg-white rounded-3xl border border-slate-200 p-8 sm:p-10 shadow-md">
        <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-100">
            <span class="w-10 h-10 rounded-xl bg-amber-400 text-slate-950 flex items-center justify-center">
                <i data-lucide="shield-check" class="w-5 h-5"></i>
            </span>
            <div>
                <h2 class="text-xl font-black text-slate-950">Activer mon abonnement CIOMAG Premium</h2>
                <p class="text-xs text-slate-500">Déverrouillage instantané de tous les articles exclusifs dès validation.</p>
            </div>
        </div>

        <form action="{{ route('magazine.subscriptions.subscribe') }}" method="POST" class="space-y-5">
            @csrf
            <input type="hidden" name="plan_id" :value="selectedPlanId">

            @guest
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Votre Nom & Prénom *</label>
                        <input type="text" name="display_name" required placeholder="Ex: Marc Kouadio" class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Email Professionnel *</label>
                        <input type="email" name="email" required placeholder="prenom.nom@entreprise.com" class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Créer un mot de passe abonné *</label>
                    <input type="password" name="password" required minlength="6" placeholder="Minimum 6 caractères" class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl">
                </div>
            @else
                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-700 flex items-center justify-between">
                    <span>Compte connecté : <strong>{{ auth()->user()->display_name }}</strong> ({{ auth()->user()->email }})</span>
                    <span class="text-emerald-600 font-bold">Prêt pour activation</span>
                </div>
            @endguest

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Mode de paiement *</label>
                <x-ui.select
                    name="payment_method"
                    :options="$paymentMethodOptions"
                    value="card"
                    :searchable="false"
                />
            </div>

            <button type="submit" class="w-full py-3.5 bg-red-700 hover:bg-red-800 text-white text-sm font-black uppercase tracking-wider rounded-xl inline-flex items-center justify-center gap-2 shadow-md transition cursor-pointer">
                <i data-lucide="crown" class="w-4 h-4"></i>
                <span>Valider et débloquer les articles exclusifs</span>
            </button>
        </form>
    </div>

    <!-- Aperçu des Dossiers Exclusifs Abonnés -->
    @if($premiumPosts->isNotEmpty())
        <section class="space-y-6">
            <div class="flex items-center justify-between border-b-2 border-slate-900 pb-3">
                <h2 class="text-xl font-black uppercase tracking-tight text-slate-950 flex items-center gap-2">
                    <i data-lucide="lock" class="w-5 h-5 text-amber-500"></i>
                    <span>Dernières Enquêtes Exclusives Abonnés</span>
                </h2>
                <span class="text-xs font-semibold text-slate-500">Incluses dans votre abonnement</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($premiumPosts as $pPost)
                    <article class="bg-white rounded-2xl border border-slate-200 p-6 shadow-2xs flex flex-col justify-between">
                        <div class="space-y-3">
                            <span class="px-2.5 py-1 text-[10px] font-extrabold uppercase bg-slate-950 text-amber-400 rounded-xs inline-flex items-center gap-1">
                                <i data-lucide="crown" class="w-3 h-3"></i>
                                <span>Exclusif Premium</span>
                            </span>
                            <h3 class="text-base font-bold text-slate-900 hover:text-red-700 leading-snug">
                                <a href="{{ route('magazine.post', $pPost->slug) }}">{{ $pPost->title }}</a>
                            </h3>
                            <p class="text-xs text-slate-600 line-clamp-3">{{ $pPost->excerpt }}</p>
                        </div>
                        <div class="pt-4 mt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                            <span>{{ $pPost->reading_time_minutes }} min de lecture</span>
                            <a href="{{ route('magazine.post', $pPost->slug) }}" class="font-bold text-red-700 hover:underline inline-flex items-center gap-1">
                                <span>Lire l'enquête</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

</div>
@endsection
