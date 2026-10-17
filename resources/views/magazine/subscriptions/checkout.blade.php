@extends('layouts.magazine')

@section('meta_title', 'Souscription ' . $plan->name . ' — CIOMAG Premium')

@section('content')
<div
    class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14"
    x-data="subscriptionCheckout({
        initialPlan: {{ Js::from($plan) }},
        allPlans: {{ Js::from($allPlans) }},
        currentUser: {{ Js::from(auth()->user() ? [
            'id' => auth()->user()->id,
            'display_name' => auth()->user()->display_name,
            'email' => auth()->user()->email,
            'is_verified' => (bool) auth()->user()->email_verified_at,
        ] : null) }},
        csrfToken: '{{ csrf_token() }}',
        sendOtpUrl: '{{ route('magazine.subscriptions.otp.send') }}',
        verifyOtpUrl: '{{ route('magazine.subscriptions.otp.verify') }}',
        resendOtpUrl: '{{ route('magazine.subscriptions.otp.resend') }}',
        processPaymentUrl: '{{ route('magazine.subscriptions.payment.process') }}',
    })"
    x-cloak
>

    <!-- Fil d'Ariane & Retour -->
    <div class="mb-8 flex items-center justify-between">
        <a href="{{ route('magazine.subscriptions.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-500 hover:text-ciomag-teal transition">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Retour aux formules d'abonnement</span>
        </a>
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-900 text-amber-400 text-xs font-black uppercase tracking-wider">
            <i data-lucide="crown" class="w-3.5 h-3.5"></i>
            <span>Club CIOMAG Premium</span>
        </span>
    </div>

    <!-- Stepper Visuel Horizontal (6 Étapes) -->
    <div class="mb-10 bg-white rounded-3xl border border-slate-200 p-4 sm:p-6 shadow-xs">
        <div class="relative">
            <!-- Barre de progression de fond -->
            <div class="hidden md:block absolute top-1/2 left-0 w-full h-1 bg-slate-100 -translate-y-1/2 z-0"></div>
            <!-- Barre de progression active -->
            <div
                class="hidden md:block absolute top-1/2 left-0 h-1 bg-amber-400 -translate-y-1/2 transition-all duration-500 z-0"
                :style="'width: ' + ((step - 1) / 5 * 100) + '%'"
            ></div>

            <div class="grid grid-cols-3 md:grid-cols-6 gap-3 sm:gap-4 relative z-10">
                <!-- Étape 1 -->
                <div class="flex flex-col items-center text-center cursor-pointer group" @click="canGoToStep(1) && (step = 1)">
                    <div
                        class="w-10 h-10 rounded-full flex items-center justify-center font-black text-xs transition duration-300 border-2"
                        :class="step > 1 ? 'bg-emerald-600 border-emerald-600 text-white' : (step === 1 ? 'bg-slate-950 border-amber-400 text-amber-400 shadow-md ring-4 ring-amber-100' : 'bg-white border-slate-300 text-slate-400')"
                    >
                        <template x-if="step > 1">
                            <i data-lucide="check" class="w-4 h-4"></i>
                        </template>
                        <template x-if="step <= 1">
                            <span>1</span>
                        </template>
                    </div>
                    <span class="mt-2 text-[11px] font-extrabold uppercase tracking-tight" :class="step === 1 ? 'text-slate-950' : 'text-slate-500'">Formule</span>
                </div>

                <!-- Étape 2 -->
                <div class="flex flex-col items-center text-center cursor-pointer group" @click="canGoToStep(2) && (step = 2)">
                    <div
                        class="w-10 h-10 rounded-full flex items-center justify-center font-black text-xs transition duration-300 border-2"
                        :class="step > 2 ? 'bg-emerald-600 border-emerald-600 text-white' : (step === 2 ? 'bg-slate-950 border-amber-400 text-amber-400 shadow-md ring-4 ring-amber-100' : 'bg-white border-slate-300 text-slate-400')"
                    >
                        <template x-if="step > 2">
                            <i data-lucide="check" class="w-4 h-4"></i>
                        </template>
                        <template x-if="step <= 2">
                            <span>2</span>
                        </template>
                    </div>
                    <span class="mt-2 text-[11px] font-extrabold uppercase tracking-tight" :class="step === 2 ? 'text-slate-950' : 'text-slate-500'">Conditions</span>
                </div>

                <!-- Étape 3 -->
                <div class="flex flex-col items-center text-center cursor-pointer group" @click="canGoToStep(3) && (step = 3)">
                    <div
                        class="w-10 h-10 rounded-full flex items-center justify-center font-black text-xs transition duration-300 border-2"
                        :class="step > 3 ? 'bg-emerald-600 border-emerald-600 text-white' : (step === 3 ? 'bg-slate-950 border-amber-400 text-amber-400 shadow-md ring-4 ring-amber-100' : 'bg-white border-slate-300 text-slate-400')"
                    >
                        <template x-if="step > 3">
                            <i data-lucide="check" class="w-4 h-4"></i>
                        </template>
                        <template x-if="step <= 3">
                            <span>3</span>
                        </template>
                    </div>
                    <span class="mt-2 text-[11px] font-extrabold uppercase tracking-tight" :class="step === 3 ? 'text-slate-950' : 'text-slate-500'">Coordonnées</span>
                </div>

                <!-- Étape 4 -->
                <div class="flex flex-col items-center text-center cursor-pointer group" @click="canGoToStep(4) && (step = 4)">
                    <div
                        class="w-10 h-10 rounded-full flex items-center justify-center font-black text-xs transition duration-300 border-2"
                        :class="step > 4 ? 'bg-emerald-600 border-emerald-600 text-white' : (step === 4 ? 'bg-slate-950 border-amber-400 text-amber-400 shadow-md ring-4 ring-amber-100' : 'bg-white border-slate-300 text-slate-400')"
                    >
                        <template x-if="step > 4">
                            <i data-lucide="check" class="w-4 h-4"></i>
                        </template>
                        <template x-if="step <= 4">
                            <span>4</span>
                        </template>
                    </div>
                    <span class="mt-2 text-[11px] font-extrabold uppercase tracking-tight" :class="step === 4 ? 'text-slate-950' : 'text-slate-500'">Validation OTP</span>
                </div>

                <!-- Étape 5 -->
                <div class="flex flex-col items-center text-center cursor-pointer group" @click="canGoToStep(5) && (step = 5)">
                    <div
                        class="w-10 h-10 rounded-full flex items-center justify-center font-black text-xs transition duration-300 border-2"
                        :class="step > 5 ? 'bg-emerald-600 border-emerald-600 text-white' : (step === 5 ? 'bg-slate-950 border-amber-400 text-amber-400 shadow-md ring-4 ring-amber-100' : 'bg-white border-slate-300 text-slate-400')"
                    >
                        <template x-if="step > 5">
                            <i data-lucide="check" class="w-4 h-4"></i>
                        </template>
                        <template x-if="step <= 5">
                            <span>5</span>
                        </template>
                    </div>
                    <span class="mt-2 text-[11px] font-extrabold uppercase tracking-tight" :class="step === 5 ? 'text-slate-950' : 'text-slate-500'">Paiement</span>
                </div>

                <!-- Étape 6 -->
                <div class="flex flex-col items-center text-center">
                    <div
                        class="w-10 h-10 rounded-full flex items-center justify-center font-black text-xs transition duration-300 border-2"
                        :class="step === 6 ? (paymentSuccess ? 'bg-emerald-600 border-emerald-500 text-white shadow-md ring-4 ring-emerald-100' : 'bg-rose-600 border-rose-500 text-white shadow-md ring-4 ring-rose-100') : 'bg-white border-slate-300 text-slate-400'"
                    >
                        <template x-if="step === 6 && paymentSuccess">
                            <i data-lucide="crown" class="w-4 h-4"></i>
                        </template>
                        <template x-if="step === 6 && !paymentSuccess">
                            <i data-lucide="alert-circle" class="w-4 h-4"></i>
                        </template>
                        <template x-if="step < 6">
                            <span>6</span>
                        </template>
                    </div>
                    <span class="mt-2 text-[11px] font-extrabold uppercase tracking-tight" :class="step === 6 ? 'text-slate-950' : 'text-slate-500'">Confirmation</span>
                </div>
            </div>
        </div>
    </div>

    <!-- CONTENU DES ÉTAPES -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-10 shadow-lg">

        <!-- ==================================================== -->
        <!-- ÉTAPE 1 : LES INFOS DE LA FORMULE                    -->
        <!-- ==================================================== -->
        <div x-show="step === 1" x-transition.opacity.duration.300ms class="space-y-8">
            <div class="flex items-start justify-between flex-wrap gap-4 border-b border-slate-100 pb-6">
                <div>
                    <span class="px-2.5 py-1 text-[10px] font-black uppercase tracking-wider rounded-md bg-amber-400 text-slate-950 inline-block mb-2" x-text="activePlan.badge || 'Formule Recommandée'"></span>
                    <h2 class="text-2xl sm:text-3xl font-black text-slate-950" x-text="activePlan.name"></h2>
                    <p class="text-sm text-slate-500 mt-1" x-text="activePlan.description"></p>
                </div>
                <div class="text-right">
                    <div class="text-3xl sm:text-4xl font-black text-slate-950" x-text="formatPrice(activePlan.price, activePlan.currency)"></div>
                    <div class="text-xs text-slate-500" x-text="'/ ' + getIntervalLabel(activePlan.billing_interval)"></div>
                </div>
            </div>

            <!-- Avantages et Privilèges inclus -->
            <div class="space-y-4">
                <h3 class="text-xs font-black uppercase tracking-wider text-slate-700 flex items-center gap-2">
                    <i data-lucide="check-check" class="w-4 h-4 text-emerald-600"></i>
                    <span>Privilèges et accès inclus dans votre offre</span>
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <template x-for="(feat, idx) in (activePlan.features || [])" :key="idx">
                        <div class="flex items-start gap-3 p-3.5 rounded-2xl bg-slate-50 border border-slate-100">
                            <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 mt-0.5">
                                <i data-lucide="check" class="w-3 h-3"></i>
                            </span>
                            <span class="text-xs font-medium text-slate-700 leading-snug" x-text="feat"></span>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Sélecteur pour changer de formule si souhaité -->
            <div class="pt-6 border-t border-slate-100">
                <label class="block text-xs font-bold text-slate-700 mb-2">Changer de formule d'abonnement :</label>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <template x-for="p in allPlans" :key="p.id">
                        <div
                            @click="selectPlan(p)"
                            class="p-4 rounded-2xl border-2 cursor-pointer transition flex flex-col justify-between"
                            :class="activePlan.id === p.id ? 'border-amber-500 bg-amber-50/40 text-slate-950 shadow-xs' : 'border-slate-200 hover:border-slate-300 bg-white'"
                        >
                            <div>
                                <div class="flex items-center justify-between text-xs font-bold">
                                    <span x-text="p.name"></span>
                                    <span class="w-4 h-4 rounded-full border flex items-center justify-center" :class="activePlan.id === p.id ? 'bg-amber-400 border-amber-400 text-slate-950' : 'border-slate-300'">
                                        <i data-lucide="check" class="w-2.5 h-2.5" x-show="activePlan.id === p.id"></i>
                                    </span>
                                </div>
                                <div class="text-lg font-black mt-2 text-slate-900" x-text="formatPrice(p.price, p.currency)"></div>
                            </div>
                            <span class="text-[10px] text-slate-500 mt-2" x-text="'/ ' + getIntervalLabel(p.billing_interval)"></span>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Bouton Étape Suivante -->
            <div class="pt-6 border-t border-slate-100 flex items-center justify-between">
                <a href="{{ route('magazine.subscriptions.index') }}" class="px-5 py-3 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition">
                    Annuler
                </a>
                <button
                    type="button"
                    @click="step = 2"
                    class="px-8 py-3.5 bg-ciomag-pine hover:bg-ciomag-teal text-white text-xs font-black uppercase tracking-wider rounded-xl inline-flex items-center gap-2 shadow-md transition cursor-pointer"
                >
                    <span>Continuer vers les conditions</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
            </div>
        </div>

        <!-- ==================================================== -->
        <!-- ÉTAPE 2 : LES TERMES ET CONDITIONS                   -->
        <!-- ==================================================== -->
        <div x-show="step === 2" x-transition.opacity.duration.300ms class="space-y-6">
            <div class="border-b border-slate-100 pb-4">
                <span class="px-2.5 py-1 text-[10px] font-black uppercase tracking-wider rounded-md bg-slate-100 text-slate-700 inline-block mb-2">Étape 2 sur 6</span>
                <h2 class="text-2xl font-black text-slate-950">Termes et Conditions Générales d'Abonnement</h2>
                <p class="text-xs text-slate-500 mt-1">Veuillez prendre connaissance des engagements éditoriaux et des conditions régissant votre accès Premium.</p>
            </div>

            <!-- Boîte de texte scrollable des conditions -->
            <div class="bg-slate-50 border border-slate-200 rounded-2xl p-5 text-xs text-slate-600 leading-relaxed max-h-72 overflow-y-auto space-y-4">
                <div>
                    <h4 class="font-black text-slate-900 uppercase text-[11px] mb-1">1. Objet du Service & Accès Numérique</h4>
                    <p>L'abonnement CIOMAG Premium confère un droit d'accès personnel, confidentiel et illimité à l'ensemble des enquêtes exclusives, dossiers d'analyses stratégiques, benchmarks des DSI et archives bimestrielles publiées par la rédaction panafricaine et internationale de CIOMAG.</p>
                </div>
                <div>
                    <h4 class="font-black text-slate-900 uppercase text-[11px] mb-1">2. Modalités de Facturation & Reconduction</h4>
                    <p>Les abonnements mensuels et annuels sont soumis à une reconduction tacite à échéance pour garantir la continuité de l'accès aux veilles d'actualité. Vous pouvez interrompre votre abonnement en un clic depuis votre espace abonné à tout moment, sans frais de résiliation.</p>
                </div>
                <div>
                    <h4 class="font-black text-slate-900 uppercase text-[11px] mb-1">3. Confidentialité & Protection des Données (RGPD & Lois Africaines)</h4>
                    <p>CIOMAG Media s'engage à ne jamais céder ni revendre vos coordonnées professionnelles à des tiers. Les données recueillies sont strictement destinées à la gestion de votre abonnement et à l'envoi de vos alertes éditoriales exclusives.</p>
                </div>
                <div>
                    <h4 class="font-black text-slate-900 uppercase text-[11px] mb-1">4. Droit de Rétractation</h4>
                    <p>Conformément aux dispositions relatives aux services numériques et contenus dématérialisés fournis immédiatement, vous bénéficiez d'une garantie de satisfaction de 14 jours avec support dédié joignable à <code>abonnements@cio-mag.com</code>.</p>
                </div>
            </div>

            <!-- Cases à cocher interactives -->
            <div class="space-y-3 pt-2">
                <label class="flex items-start gap-3 p-3.5 rounded-xl border border-slate-200 hover:bg-slate-50/50 cursor-pointer transition">
                    <input type="checkbox" x-model="termsAccepted" class="w-4 h-4 rounded text-ciomag-teal border-slate-300 focus:ring-ciomag-teal mt-0.5">
                    <span class="text-xs text-slate-700 leading-snug">
                        <strong>J'accepte sans réserve les Conditions Générales d'Abonnement (CGA)</strong> et la Politique de Confidentialité de CIOMAG Media. <span class="text-ciomag-crimson">*</span>
                    </span>
                </label>

                <label class="flex items-start gap-3 p-3.5 rounded-xl border border-slate-200 hover:bg-slate-50/50 cursor-pointer transition">
                    <input type="checkbox" x-model="newsletterOptIn" class="w-4 h-4 rounded text-ciomag-teal border-slate-300 focus:ring-ciomag-teal mt-0.5">
                    <span class="text-xs text-slate-700 leading-snug">
                        Je souhaite recevoir la veille hebdomadaire des décideurs IT et les invitations aux tables rondes privées organisées par CIOMAG.
                    </span>
                </label>
            </div>

            <!-- Navigation -->
            <div class="pt-6 border-t border-slate-100 flex items-center justify-between">
                <button
                    type="button"
                    @click="step = 1"
                    class="px-5 py-3 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition inline-flex items-center gap-1.5"
                >
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    <span>Précédent</span>
                </button>
                <button
                    type="button"
                    @click="step = 3"
                    :disabled="!termsAccepted"
                    class="px-8 py-3.5 bg-slate-950 hover:bg-ciomag-teal disabled:opacity-50 disabled:pointer-events-none text-white text-xs font-black uppercase tracking-wider rounded-xl inline-flex items-center gap-2 shadow-md transition cursor-pointer"
                >
                    <span>Accepter et continuer</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
            </div>
        </div>

        <!-- ==================================================== -->
        <!-- ÉTAPE 3 : INFORMATIONS PERSONNELLES                  -->
        <!-- ==================================================== -->
        <div x-show="step === 3" x-transition.opacity.duration.300ms class="space-y-6">
            <div class="border-b border-slate-100 pb-4">
                <span class="px-2.5 py-1 text-[10px] font-black uppercase tracking-wider rounded-md bg-slate-100 text-slate-700 inline-block mb-2">Étape 3 sur 6</span>
                <h2 class="text-2xl font-black text-slate-950">Vos Informations Personnelles & Professionnelles</h2>
                <p class="text-xs text-slate-500 mt-1">À la soumission, votre compte abonné sera initié et un code de validation OTP à 6 chiffres vous sera envoyé par email.</p>
            </div>

            <!-- Message d'erreur s'il y a lieu -->
            <div x-show="step3Error" class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-xs font-bold text-rose-800 flex items-center gap-3">
                <i data-lucide="alert-triangle" class="w-5 h-5 text-rose-600 shrink-0"></i>
                <span x-text="step3Error"></span>
            </div>

            <form @submit.prevent="submitPersonalInfo" class="space-y-4">
                <template x-if="currentUser">
                    <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-xs text-amber-950 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i data-lucide="user-check" class="w-5 h-5 text-amber-600"></i>
                            <div>
                                <span class="font-bold">Vous êtes connecté en tant que : </span>
                                <span x-text="currentUser.display_name"></span> (<span x-text="currentUser.email"></span>)
                            </div>
                        </div>
                        <span class="px-2.5 py-1 rounded-full bg-amber-200 text-[10px] font-black uppercase tracking-wider">Compte Actif</span>
                    </div>
                </template>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nom & Prénom <span class="text-rose-600">*</span></label>
                        <input
                            type="text"
                            x-model="personalInfo.display_name"
                            required
                            placeholder="Ex: Marc Kouadio"
                            class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:border-ciomag-teal focus:ring-1 focus:ring-ciomag-teal transition"
                        >
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Email Professionnel <span class="text-rose-600">*</span></label>
                        <input
                            type="email"
                            x-model="personalInfo.email"
                            required
                            :disabled="currentUser !== null"
                            placeholder="prenom.nom@entreprise.com"
                            class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:border-ciomag-teal focus:ring-1 focus:ring-ciomag-teal disabled:bg-slate-100 disabled:text-slate-500 transition"
                        >
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Entreprise / Organisation</label>
                        <input
                            type="text"
                            x-model="personalInfo.company"
                            placeholder="Ex: Banque Centrale, Ministère, Télécoms..."
                            class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:border-ciomag-teal focus:ring-1 focus:ring-ciomag-teal transition"
                        >
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Fonction / Titre</label>
                        <input
                            type="text"
                            x-model="personalInfo.job_title"
                            placeholder="Ex: DSI, CTO, RSSI, Chef de Projet..."
                            class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:border-ciomag-teal focus:ring-1 focus:ring-ciomag-teal transition"
                        >
                    </div>
                </div>

                <template x-if="!currentUser">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Mot de passe abonné <span class="text-rose-600">*</span></label>
                            <input
                                type="password"
                                x-model="personalInfo.password"
                                required
                                minlength="6"
                                placeholder="Au moins 6 caractères"
                                class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:border-ciomag-teal focus:ring-1 focus:ring-ciomag-teal transition"
                            >
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Confirmer le mot de passe <span class="text-rose-600">*</span></label>
                            <input
                                type="password"
                                x-model="personalInfo.password_confirmation"
                                required
                                minlength="6"
                                placeholder="Retapez votre mot de passe"
                                class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:border-ciomag-teal focus:ring-1 focus:ring-ciomag-teal transition"
                            >
                        </div>
                    </div>
                </template>

                <div class="pt-6 border-t border-slate-100 flex items-center justify-between">
                    <button
                        type="button"
                        @click="step = 2"
                        class="px-5 py-3 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition inline-flex items-center gap-1.5"
                    >
                        <i data-lucide="arrow-left" class="w-4 h-4"></i>
                        <span>Précédent</span>
                    </button>
                    <button
                        type="submit"
                        :disabled="isSubmittingStep3"
                        class="px-8 py-3.5 bg-ciomag-teal hover:bg-ciomag-pine disabled:opacity-50 text-white text-xs font-black uppercase tracking-wider rounded-xl inline-flex items-center gap-2 shadow-md transition cursor-pointer"
                    >
                        <template x-if="isSubmittingStep3">
                            <span class="inline-flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                <span>Génération du code OTP...</span>
                            </span>
                        </template>
                        <template x-if="!isSubmittingStep3">
                            <span class="inline-flex items-center gap-2">
                                <i data-lucide="mail-check" class="w-4 h-4"></i>
                                <span>Envoyer le code OTP de validation</span>
                            </span>
                        </template>
                    </button>
                </div>
            </form>
        </div>

        <!-- ==================================================== -->
        <!-- ÉTAPE 4 : VÉRIFICATION DE L'EMAIL (CODE OTP)          -->
        <!-- ==================================================== -->
        <div x-show="step === 4" x-transition.opacity.duration.300ms class="space-y-6">
            <div class="text-center max-w-lg mx-auto space-y-2">
                <span class="w-14 h-14 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center mx-auto mb-3">
                    <i data-lucide="mail" class="w-7 h-7"></i>
                </span>
                <span class="px-2.5 py-1 text-[10px] font-black uppercase tracking-wider rounded-md bg-slate-100 text-slate-700 inline-block">Étape 4 sur 6</span>
                <h2 class="text-2xl font-black text-slate-950">Vérification de votre Email</h2>
                <p class="text-xs text-slate-600">
                    Un code de sécurité à 6 chiffres a été envoyé à l'adresse <strong class="text-slate-950 font-bold" x-text="maskedEmail"></strong>.
                </p>
                <p class="text-[11px] text-slate-500">Saisissez ce code pour valider votre email et accéder au paiement sécurisé.</p>
            </div>

            <!-- Message d'erreur OTP -->
            <div x-show="otpError" class="max-w-md mx-auto p-4 rounded-2xl bg-rose-50 border border-rose-200 text-xs font-bold text-rose-800 flex items-center gap-3">
                <i data-lucide="alert-triangle" class="w-5 h-5 text-rose-600 shrink-0"></i>
                <span x-text="otpError"></span>
            </div>

            <!-- Message de succès temporaire -->
            <div x-show="otpSuccessMessage" class="max-w-md mx-auto p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-xs font-bold text-emerald-800 flex items-center gap-3">
                <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600 shrink-0"></i>
                <span x-text="otpSuccessMessage"></span>
            </div>

            <!-- Grille des 6 chiffres OTP -->
            <div class="max-w-sm mx-auto flex justify-center gap-2 sm:gap-3 my-4">
                <template x-for="(digit, idx) in otpDigits" :key="idx">
                    <input
                        type="text"
                        inputmode="numeric"
                        pattern="[0-9]*"
                        maxlength="1"
                        :id="'otp-box-' + idx"
                        x-model="otpDigits[idx]"
                        @input="handleOtpInput(idx, $event)"
                        @keydown.backspace="handleOtpBackspace(idx, $event)"
                        @paste="handleOtpPaste($event)"
                        class="w-12 h-14 sm:w-14 sm:h-16 text-center text-2xl font-black text-slate-950 bg-slate-50 border-2 border-slate-300 rounded-2xl focus:bg-white focus:border-ciomag-teal focus:ring-2 focus:ring-ciomag-teal/20 outline-none transition"
                    >
                </template>
            </div>

            <!-- Compte à rebours / Renvoyer le code -->
            <div class="text-center text-xs text-slate-500 space-y-1">
                <p>Vous n'avez pas reçu le code ?</p>
                <div>
                    <template x-if="resendCountdown > 0">
                        <span class="text-slate-400">Renvoyer un nouveau code dans <strong class="text-slate-700 font-bold" x-text="resendCountdown"></strong> s</span>
                    </template>
                    <template x-if="resendCountdown === 0">
                        <button
                            type="button"
                            @click="resendOtpCode"
                            :disabled="isResendingOtp"
                            class="font-black text-ciomag-teal hover:text-ciomag-pine hover:underline cursor-pointer disabled:opacity-50"
                        >
                            <span>Renvoyer un nouveau code OTP</span>
                        </button>
                    </template>
                </div>
            </div>

            <!-- Navigation -->
            <div class="pt-6 border-t border-slate-100 flex items-center justify-between">
                <button
                    type="button"
                    @click="step = 3"
                    class="px-5 py-3 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition inline-flex items-center gap-1.5"
                >
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    <span>Modifier mes informations</span>
                </button>
                <button
                    type="button"
                    @click="verifyOtpCode"
                    :disabled="isVerifyingOtp || getFullOtpCode().length !== 6"
                    class="px-8 py-3.5 bg-slate-950 hover:bg-ciomag-teal disabled:opacity-50 text-white text-xs font-black uppercase tracking-wider rounded-xl inline-flex items-center gap-2 shadow-md transition cursor-pointer"
                >
                    <template x-if="isVerifyingOtp">
                        <span class="inline-flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            <span>Vérification...</span>
                        </span>
                    </template>
                    <template x-if="!isVerifyingOtp">
                        <span class="inline-flex items-center gap-2">
                            <span>Vérifier & Passer au paiement</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </span>
                    </template>
                </button>
            </div>
        </div>

        <!-- ==================================================== -->
        <!-- ÉTAPE 5 : INFORMATIONS DE PAIEMENT                   -->
        <!-- ==================================================== -->
        <div x-show="step === 5" x-transition.opacity.duration.300ms class="space-y-6">
            <div class="border-b border-slate-100 pb-4 flex items-start justify-between flex-wrap gap-4">
                <div>
                    <span class="px-2.5 py-1 text-[10px] font-black uppercase tracking-wider rounded-md bg-slate-100 text-slate-700 inline-block mb-2">Étape 5 sur 6</span>
                    <h2 class="text-2xl font-black text-slate-950">Règlement Sécurisé de votre Abonnement</h2>
                    <p class="text-xs text-slate-500 mt-1">Choisissez votre mode de paiement pour débloquer immédiatement vos accès exclusifs.</p>
                </div>
                <div class="bg-emerald-50 border border-emerald-200 rounded-2xl px-4 py-2 flex items-center gap-2 text-xs text-emerald-900 font-bold">
                    <i data-lucide="badge-check" class="w-4 h-4 text-emerald-600"></i>
                    <span>Email certifié : <strong x-text="personalInfo.email"></strong></span>
                </div>
            </div>

            <!-- Récapitulatif de Commande Express -->
            <div class="p-4 sm:p-5 rounded-2xl bg-slate-950 text-white flex items-center justify-between flex-wrap gap-4">
                <div>
                    <span class="text-xs font-extrabold uppercase text-amber-400" x-text="activePlan.name"></span>
                    <p class="text-[11px] text-slate-400 mt-0.5" x-text="'Périodicité : ' + getIntervalLabel(activePlan.billing_interval) + ' — Résiliation en 1 clic'"></p>
                </div>
                <div class="text-right">
                    <span class="text-2xl sm:text-3xl font-black text-amber-400" x-text="formatPrice(activePlan.price, activePlan.currency)"></span>
                    <span class="block text-[10px] text-slate-400">TTC / Tous droits inclus</span>
                </div>
            </div>

            <!-- Sélecteur de méthode de paiement -->
            <div class="space-y-4">
                <label class="block text-xs font-black uppercase tracking-wider text-slate-700">Sélectionnez votre moyen de paiement :</label>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <!-- Carte Bancaire -->
                    <div
                        @click="paymentData.method = 'card'"
                        class="p-4 rounded-2xl border-2 cursor-pointer transition flex flex-col justify-between"
                        :class="paymentData.method === 'card' ? 'border-ciomag-teal bg-ciomag-teal-pale/40 text-slate-950 shadow-xs' : 'border-slate-200 hover:border-slate-300 bg-white'"
                    >
                        <div class="flex items-center justify-between mb-2">
                            <i data-lucide="credit-card" class="w-5 h-5 text-ciomag-teal"></i>
                            <span class="w-4 h-4 rounded-full border flex items-center justify-center" :class="paymentData.method === 'card' ? 'bg-ciomag-teal border-ciomag-teal text-white' : 'border-slate-300'">
                                <i data-lucide="check" class="w-2.5 h-2.5" x-show="paymentData.method === 'card'"></i>
                            </span>
                        </div>
                        <div>
                            <span class="text-xs font-black block">Carte Bancaire Visa / Mastercard</span>
                            <span class="text-[10px] text-slate-500">Stripe & 3D Secure 2.0</span>
                        </div>
                    </div>

                    <!-- Mobile Money -->
                    <div
                        @click="paymentData.method = 'mobile_money'"
                        class="p-4 rounded-2xl border-2 cursor-pointer transition flex flex-col justify-between"
                        :class="paymentData.method === 'mobile_money' ? 'border-ciomag-amber bg-ciomag-amber-light text-slate-950 shadow-xs' : 'border-slate-200 hover:border-slate-300 bg-white'"
                    >
                        <div class="flex items-center justify-between mb-2">
                            <i data-lucide="smartphone" class="w-5 h-5 text-ciomag-amber"></i>
                            <span class="w-4 h-4 rounded-full border flex items-center justify-center" :class="paymentData.method === 'mobile_money' ? 'bg-ciomag-amber border-ciomag-amber text-slate-950' : 'border-slate-300'">
                                <i data-lucide="check" class="w-2.5 h-2.5" x-show="paymentData.method === 'mobile_money'"></i>
                            </span>
                        </div>
                        <div>
                            <span class="text-xs font-black block">Mobile Money</span>
                            <span class="text-[10px] text-slate-500">Orange / Wave / MTN / Moov</span>
                        </div>
                    </div>

                    <!-- Facturation Entreprise -->
                    <div
                        @click="paymentData.method = 'corporate_invoice'"
                        class="p-4 rounded-2xl border-2 cursor-pointer transition flex flex-col justify-between"
                        :class="paymentData.method === 'corporate_invoice' ? 'border-slate-900 bg-slate-50 text-slate-950 shadow-xs' : 'border-slate-200 hover:border-slate-300 bg-white'"
                    >
                        <div class="flex items-center justify-between mb-2">
                            <i data-lucide="building-2" class="w-5 h-5 text-slate-900"></i>
                            <span class="w-4 h-4 rounded-full border flex items-center justify-center" :class="paymentData.method === 'corporate_invoice' ? 'bg-slate-950 border-slate-950 text-white' : 'border-slate-300'">
                                <i data-lucide="check" class="w-2.5 h-2.5" x-show="paymentData.method === 'corporate_invoice'"></i>
                            </span>
                        </div>
                        <div>
                            <span class="text-xs font-black block">Facturation Entreprise</span>
                            <span class="text-[10px] text-slate-500">Bon de commande & Virement</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulaire spécifique selon la méthode choisie -->
            <div class="bg-slate-50 border border-slate-200 rounded-2xl p-6">
                <!-- Option 1 : Formulaire Carte Bancaire -->
                <div x-show="paymentData.method === 'card'" class="space-y-4">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-200">
                        <span class="text-xs font-black text-slate-800 uppercase tracking-wider">Informations de la Carte Bancaire</span>
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-900 text-[10px] font-black">VISA</span>
                            <span class="px-2 py-0.5 rounded bg-orange-100 text-orange-900 text-[10px] font-black">MASTERCARD</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nom sur la Carte *</label>
                        <input
                            type="text"
                            x-model="paymentData.card_name"
                            placeholder="Ex: MARC KOUADIO"
                            class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl bg-white focus:border-ciomag-teal focus:ring-1 focus:ring-ciomag-teal uppercase"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Numéro de Carte *</label>
                        <div class="relative">
                            <input
                                type="text"
                                x-model="paymentData.card_number"
                                @input="formatCardNumber($event)"
                                maxlength="19"
                                placeholder="4000 0000 0000 0000"
                                class="w-full pl-3.5 pr-10 py-2.5 text-sm font-mono tracking-wider border border-slate-300 rounded-xl bg-white focus:border-ciomag-teal focus:ring-1 focus:ring-ciomag-teal"
                            >
                            <i data-lucide="lock" class="w-4 h-4 text-slate-400 absolute right-3.5 top-3"></i>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Expiration (MM/AA) *</label>
                            <input
                                type="text"
                                x-model="paymentData.card_expiry"
                                @input="formatCardExpiry($event)"
                                maxlength="5"
                                placeholder="MM/AA"
                                class="w-full px-3.5 py-2.5 text-sm font-mono text-center border border-slate-300 rounded-xl bg-white focus:border-ciomag-teal focus:ring-1 focus:ring-ciomag-teal"
                            >
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Code CVC / CVV *</label>
                            <input
                                type="text"
                                x-model="paymentData.card_cvc"
                                maxlength="4"
                                placeholder="123"
                                class="w-full px-3.5 py-2.5 text-sm font-mono text-center border border-slate-300 rounded-xl bg-white focus:border-ciomag-teal focus:ring-1 focus:ring-ciomag-teal"
                            >
                        </div>
                    </div>

                    <!-- Simulateur Sandbox pour tester le succès OU l'échec -->
                    <div class="mt-4 pt-3 border-t border-slate-200">
                        <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-600">
                            <input type="checkbox" x-model="paymentData.simulate_failure" class="w-4 h-4 rounded text-rose-600 border-slate-300 focus:ring-rose-500">
                            <span class="font-bold text-rose-700">Mode Test : Simuler un échec de paiement (pour tester l'étape 6 et le retour en arrière)</span>
                        </label>
                    </div>
                </div>

                <!-- Option 2 : Formulaire Mobile Money -->
                <div x-show="paymentData.method === 'mobile_money'" class="space-y-4">
                    <div class="pb-2 border-b border-slate-200">
                        <span class="text-xs font-black text-slate-800 uppercase tracking-wider">Règlement par Mobile Money (Afrique)</span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Opérateur Réseau *</label>
                        <select x-model="paymentData.mobile_operator" class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl bg-white focus:border-ciomag-teal">
                            <option value="Orange Money">Orange Money</option>
                            <option value="Wave">Wave</option>
                            <option value="MTN MoMo">MTN Mobile Money</option>
                            <option value="Moov Money">Moov Money</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Numéro de Téléphone Mobile Money *</label>
                        <input
                            type="tel"
                            x-model="paymentData.mobile_phone"
                            placeholder="+225 07 00 00 00 00"
                            class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl bg-white focus:border-ciomag-teal"
                        >
                    </div>

                    <p class="text-[11px] text-slate-500">Une notification de validation sera envoyée directement sur votre téléphone avec le montant exact de <span class="font-bold text-slate-900" x-text="formatPrice(activePlan.price, activePlan.currency)"></span>.</p>
                </div>

                <!-- Option 3 : Facturation Entreprise -->
                <div x-show="paymentData.method === 'corporate_invoice'" class="space-y-4">
                    <div class="pb-2 border-b border-slate-200">
                        <span class="text-xs font-black text-slate-800 uppercase tracking-wider">Facturation & Bon de Commande Entreprise</span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Raison Sociale de l'Organisation *</label>
                        <input
                            type="text"
                            x-model="paymentData.company_name"
                            placeholder="Nom officiel de l'entreprise"
                            class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl bg-white focus:border-ciomag-teal"
                        >
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Numéro Fiscal / SIRET / RCCM</label>
                            <input
                                type="text"
                                x-model="paymentData.company_siret"
                                placeholder="Identifiant légal"
                                class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl bg-white focus:border-ciomag-teal"
                            >
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Adresse de Facturation</label>
                            <input
                                type="text"
                                x-model="paymentData.company_address"
                                placeholder="Siège social, Ville, Pays"
                                class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl bg-white focus:border-ciomag-teal"
                            >
                        </div>
                    </div>
                </div>
            </div>

            <!-- Boutons de soumission Paiement -->
            <div class="pt-6 border-t border-slate-100 flex items-center justify-between">
                <button
                    type="button"
                    @click="step = 4"
                    class="px-5 py-3 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition inline-flex items-center gap-1.5"
                >
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    <span>Précédent</span>
                </button>
                <button
                    type="button"
                    @click="processPaymentSubmit"
                    :disabled="isProcessingPayment"
                    class="px-8 py-3.5 bg-ciomag-amber hover:bg-ciomag-amber-hover text-slate-950 disabled:opacity-50 text-xs font-black uppercase tracking-wider rounded-xl inline-flex items-center gap-2 shadow-lg transition cursor-pointer"
                >
                    <template x-if="isProcessingPayment">
                        <span class="inline-flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            <span>Traitement sécurisé en cours...</span>
                        </span>
                    </template>
                    <template x-if="!isProcessingPayment">
                        <span class="inline-flex items-center gap-2">
                            <i data-lucide="shield-check" class="w-4 h-4"></i>
                            <span>Régler <span x-text="formatPrice(activePlan.price, activePlan.currency)"></span> & Activer mon Accès</span>
                        </span>
                    </template>
                </button>
            </div>
        </div>

        <!-- ==================================================== -->
        <!-- ÉTAPE 6 : PAGE DE SUCCÈS OU ÉCHEC DE PAIEMENT         -->
        <!-- ==================================================== -->
        <div x-show="step === 6" x-transition.opacity.duration.300ms class="space-y-6">

            <!-- CAS 1 : SUCCÈS DU PAIEMENT -->
            <template x-if="paymentSuccess">
                <div class="text-center max-w-xl mx-auto space-y-6 py-4">
                    <div class="w-20 h-20 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto shadow-inner">
                        <i data-lucide="check-check" class="w-10 h-10"></i>
                    </div>

                    <div class="space-y-2">
                        <span class="px-3 py-1 rounded-full bg-emerald-50 text-emerald-800 text-xs font-black uppercase tracking-wider border border-emerald-200">
                            Paiement validé avec succès
                        </span>
                        <h2 class="text-3xl font-black text-slate-950">Bienvenue dans le Club CIOMAG Premium !</h2>
                        <p class="text-sm text-slate-600">
                            Félicitations <strong x-text="subscriptionResult.user_name"></strong>, votre souscription à la formule <strong x-text="subscriptionResult.plan_name"></strong> est immédiatement active.
                        </p>
                    </div>

                    <!-- Récapitulatif de la transaction -->
                    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-6 text-left space-y-3">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                            <span class="text-xs text-slate-500">Référence de Transaction</span>
                            <span class="font-mono font-bold text-xs text-slate-950" x-text="subscriptionResult.reference"></span>
                        </div>
                        <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                            <span class="text-xs text-slate-500">Formule & Période</span>
                            <span class="font-bold text-xs text-slate-950" x-text="subscriptionResult.plan_name + ' (' + subscriptionResult.interval + ')'"></span>
                        </div>
                        <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                            <span class="text-xs text-slate-500">Montant Réglé</span>
                            <span class="font-black text-sm text-emerald-600" x-text="subscriptionResult.amount_formatted"></span>
                        </div>
                        <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                            <span class="text-xs text-slate-500">Moyen de Paiement</span>
                            <span class="font-semibold text-xs text-slate-800" x-text="subscriptionResult.payment_method_label"></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-slate-500">Validité</span>
                            <span class="font-bold text-xs text-slate-950" x-text="'Jusqu\'au ' + subscriptionResult.expires_at_formatted"></span>
                        </div>
                    </div>

                    <p class="text-xs text-slate-500">
                        Un reçu fiscal détaillé ainsi que vos identifiants d'accès ont été expédiés à <strong x-text="subscriptionResult.user_email"></strong>.
                    </p>

                    <!-- Boutons d'action -->
                    <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-3">
                        <a
                            href="{{ route('magazine.home') }}"
                            class="w-full sm:w-auto px-6 py-3.5 bg-ciomag-teal hover:bg-ciomag-pine text-white text-xs font-black uppercase tracking-wider rounded-xl inline-flex items-center justify-center gap-2 shadow-md transition"
                        >
                            <i data-lucide="sparkles" class="w-4 h-4"></i>
                            <span>Découvrir les enquêtes Premium</span>
                        </a>
                        <a
                            href="{{ route('magazine.subscriptions.index') }}"
                            class="w-full sm:w-auto px-6 py-3.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold uppercase tracking-wider rounded-xl inline-flex items-center justify-center gap-2 transition"
                        >
                            <span>Espace Abonnements</span>
                        </a>
                    </div>
                </div>
            </template>

            <!-- CAS 2 : ÉCHEC DU PAIEMENT AVEC OPTION DE RETOUR EN ARRIÈRE -->
            <template x-if="!paymentSuccess">
                <div class="text-center max-w-xl mx-auto space-y-6 py-4">
                    <div class="w-20 h-20 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto shadow-inner">
                        <i data-lucide="x-circle" class="w-10 h-10"></i>
                    </div>

                    <div class="space-y-2">
                        <span class="px-3 py-1 rounded-full bg-rose-50 text-rose-800 text-xs font-black uppercase tracking-wider border border-rose-200">
                            Échec de la transaction
                        </span>
                        <h2 class="text-3xl font-black text-slate-950">Le paiement n'a pas pu aboutir</h2>
                        <p class="text-sm text-slate-600">
                            Une erreur est survenue lors du débit de votre compte. Aucun montant n'a été prélevé.
                        </p>
                    </div>

                    <!-- Motif détaillé du refus -->
                    <div class="p-5 rounded-2xl bg-rose-50 border border-rose-200 text-left space-y-2">
                        <div class="flex items-start gap-2.5">
                            <i data-lucide="alert-circle" class="w-5 h-5 text-rose-600 shrink-0 mt-0.5"></i>
                            <div>
                                <span class="font-bold text-xs text-rose-950">Motif du refus bancaire :</span>
                                <p class="text-xs text-rose-800 mt-1 leading-relaxed" x-text="paymentErrorMessage || 'La transaction a été refusée par l\'émetteur ou l\'authentification 3D Secure a échoué.'"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Conseils pour débloquer la situation -->
                    <div class="text-left bg-slate-50 border border-slate-200 rounded-2xl p-5 text-xs text-slate-600 space-y-2">
                        <span class="font-black text-slate-900 uppercase text-[11px] block">Que pouvez-vous faire ?</span>
                        <ul class="list-disc pl-5 space-y-1.5">
                            <li>Vérifier le numéro de carte, la date d'expiration et le code de sécurité CVC.</li>
                            <li>Vérifier votre plafond de paiement en ligne auprès de votre établissement bancaire.</li>
                            <li>Opter pour un autre mode de paiement (Mobile Money Orange/Wave/MTN ou Facturation Entreprise).</li>
                        </ul>
                    </div>

                    <!-- BOUTON CLÉ : REVENIR EN ARRIÈRE EN CAS D'ÉCHEC -->
                    <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-3">
                        <button
                            type="button"
                            @click="retryPayment"
                            class="w-full sm:w-auto px-8 py-3.5 bg-ciomag-teal hover:bg-ciomag-pine text-white text-xs font-black uppercase tracking-wider rounded-xl inline-flex items-center justify-center gap-2 shadow-lg transition cursor-pointer"
                        >
                            <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                            <span>Revenir en arrière et réessayer le paiement</span>
                        </button>
                        <button
                            type="button"
                            @click="step = 1"
                            class="w-full sm:w-auto px-6 py-3.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold uppercase tracking-wider rounded-xl inline-flex items-center justify-center gap-2 transition cursor-pointer"
                        >
                            <span>Changer de formule</span>
                        </button>
                    </div>
                </div>
            </template>
        </div>

    </div>

</div>

<!-- Script Alpine.js pour la gestion du Wizard Stepper Multi-étapes -->
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('subscriptionCheckout', (config) => ({
        step: 1,
        activePlan: config.initialPlan,
        allPlans: config.allPlans,
        currentUser: config.currentUser,
        termsAccepted: false,
        newsletterOptIn: true,

        // Données étape 3
        personalInfo: {
            display_name: config.currentUser ? config.currentUser.display_name : '',
            email: config.currentUser ? config.currentUser.email : '',
            password: '',
            password_confirmation: '',
            company: '',
            job_title: '',
        },
        step3Error: '',
        isSubmittingStep3: false,

        // Données étape 4 (OTP)
        otpDigits: ['', '', '', '', '', ''],
        maskedEmail: '',
        otpError: '',
        otpSuccessMessage: '',
        isVerifyingOtp: false,
        isResendingOtp: false,
        resendCountdown: 60,
        countdownInterval: null,
        isEmailVerified: config.currentUser ? config.currentUser.is_verified : false,

        // Données étape 5 (Paiement)
        paymentData: {
            method: 'card',
            card_name: '',
            card_number: '',
            card_expiry: '',
            card_cvc: '',
            simulate_failure: false,
            mobile_phone: '',
            mobile_operator: 'Orange Money',
            company_name: '',
            company_siret: '',
            company_address: '',
        },
        isProcessingPayment: false,

        // Données étape 6 (Confirmation / Échec)
        paymentSuccess: false,
        paymentErrorMessage: '',
        subscriptionResult: {
            reference: '',
            plan_name: '',
            amount_formatted: '',
            interval: '',
            expires_at_formatted: '',
            user_name: '',
            user_email: '',
            payment_method_label: '',
        },

        init() {
            if (this.currentUser) {
                this.personalInfo.display_name = this.currentUser.display_name;
                this.personalInfo.email = this.currentUser.email;
                this.paymentData.card_name = this.currentUser.display_name.toUpperCase();
            }
            this.$watch('step', () => {
                this.$nextTick(() => {
                    if (window.lucide) {
                        window.lucide.createIcons();
                    }
                });
            });
        },

        canGoToStep(targetStep) {
            if (targetStep === 1) return true;
            if (targetStep === 2) return true;
            if (targetStep === 3) return this.termsAccepted;
            if (targetStep === 4) return this.maskedEmail !== '';
            if (targetStep === 5) return this.isEmailVerified;
            return false;
        },

        selectPlan(plan) {
            this.activePlan = plan;
        },

        formatPrice(price, currency) {
            const num = parseFloat(price) || 0;
            return num.toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ' + (currency === 'EUR' ? '€' : currency);
        },

        getIntervalLabel(interval) {
            switch (interval) {
                case 'monthly': return 'par mois';
                case 'yearly': return 'par an';
                case 'lifetime': return 'accès illimité';
                default: return interval;
            }
        },

        // Soumission Étape 3 : Création de compte & Envoi du code OTP
        async submitPersonalInfo() {
            this.step3Error = '';

            if (!this.currentUser && this.personalInfo.password !== this.personalInfo.password_confirmation) {
                this.step3Error = 'Les deux mots de passe ne correspondent pas.';
                return;
            }

            this.isSubmittingStep3 = true;

            try {
                const response = await fetch(config.sendOtpUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': config.csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        plan_id: this.activePlan.id,
                        display_name: this.personalInfo.display_name,
                        email: this.personalInfo.email,
                        password: this.personalInfo.password || null,
                        company: this.personalInfo.company || null,
                        job_title: this.personalInfo.job_title || null,
                    }),
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    this.step3Error = data.message || 'Une erreur est survenue lors de l\'envoi du code OTP.';
                    this.isSubmittingStep3 = false;
                    return;
                }

                this.maskedEmail = data.masked_email || this.personalInfo.email;
                this.step = 4;
                this.startResendCountdown();

                this.$nextTick(() => {
                    const firstBox = document.getElementById('otp-box-0');
                    if (firstBox) firstBox.focus();
                });
            } catch (err) {
                this.step3Error = 'Connexion interrompue avec le serveur. Veuillez réessayer.';
            } finally {
                this.isSubmittingStep3 = false;
            }
        },

        // Étape 4 : Gestion des chiffres OTP
        handleOtpInput(idx, event) {
            const val = event.target.value.replace(/[^0-9]/g, '');
            this.otpDigits[idx] = val ? val.slice(-1) : '';

            if (val && idx < 5) {
                const nextBox = document.getElementById('otp-box-' + (idx + 1));
                if (nextBox) nextBox.focus();
            }

            if (this.getFullOtpCode().length === 6) {
                this.verifyOtpCode();
            }
        },

        handleOtpBackspace(idx, event) {
            if (!this.otpDigits[idx] && idx > 0) {
                const prevBox = document.getElementById('otp-box-' + (idx - 1));
                if (prevBox) prevBox.focus();
            }
        },

        handleOtpPaste(event) {
            event.preventDefault();
            const pasteData = (event.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g, '');
            if (pasteData) {
                for (let i = 0; i < 6; i++) {
                    this.otpDigits[i] = pasteData[i] || '';
                }
                const focusIdx = Math.min(pasteData.length, 5);
                const focusBox = document.getElementById('otp-box-' + focusIdx);
                if (focusBox) focusBox.focus();

                if (this.getFullOtpCode().length === 6) {
                    this.verifyOtpCode();
                }
            }
        },

        getFullOtpCode() {
            return this.otpDigits.join('');
        },

        startResendCountdown() {
            this.resendCountdown = 60;
            if (this.countdownInterval) clearInterval(this.countdownInterval);
            this.countdownInterval = setInterval(() => {
                if (this.resendCountdown > 0) {
                    this.resendCountdown--;
                } else {
                    clearInterval(this.countdownInterval);
                }
            }, 1000);
        },

        async resendOtpCode() {
            this.isResendingOtp = true;
            this.otpError = '';

            try {
                const response = await fetch(config.resendOtpUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': config.csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        email: this.personalInfo.email,
                        plan_id: this.activePlan.id,
                    }),
                });

                const data = await response.json();
                if (data.success) {
                    this.otpSuccessMessage = data.message;
                    setTimeout(() => { this.otpSuccessMessage = ''; }, 4000);
                    this.startResendCountdown();
                } else {
                    this.otpError = data.message || 'Impossible de renvoyer le code pour le moment.';
                }
            } catch (e) {
                this.otpError = 'Erreur lors du renvoi du code.';
            } finally {
                this.isResendingOtp = false;
            }
        },

        async verifyOtpCode() {
            const code = this.getFullOtpCode();
            if (code.length !== 6) return;

            this.isVerifyingOtp = true;
            this.otpError = '';

            try {
                const response = await fetch(config.verifyOtpUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': config.csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        email: this.personalInfo.email,
                        code: code,
                    }),
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    this.otpError = data.message || 'Le code saisi est incorrect ou a expiré.';
                    this.isVerifyingOtp = false;
                    return;
                }

                this.isEmailVerified = true;
                this.step = 5;
            } catch (err) {
                this.otpError = 'Erreur de communication avec le serveur. Veuillez réessayer.';
            } finally {
                this.isVerifyingOtp = false;
            }
        },

        // Formatage Carte Bancaire
        formatCardNumber(event) {
            let val = event.target.value.replace(/\D/g, '');
            val = val.substring(0, 16);
            const parts = [];
            for (let i = 0; i < val.length; i += 4) {
                parts.push(val.substring(i, i + 4));
            }
            this.paymentData.card_number = parts.join(' ');
        },

        formatCardExpiry(event) {
            let val = event.target.value.replace(/\D/g, '');
            if (val.length >= 2) {
                this.paymentData.card_expiry = val.substring(0, 2) + '/' + val.substring(2, 4);
            } else {
                this.paymentData.card_expiry = val;
            }
        },

        // Soumission Étape 5 : Traitement du paiement
        async processPaymentSubmit() {
            this.isProcessingPayment = true;

            try {
                const response = await fetch(config.processPaymentUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': config.csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        plan_id: this.activePlan.id,
                        payment_method: this.paymentData.method,
                        simulate_failure: this.paymentData.simulate_failure,
                        card_name: this.paymentData.card_name,
                        card_number: this.paymentData.card_number,
                        card_expiry: this.paymentData.card_expiry,
                        card_cvc: this.paymentData.card_cvc,
                        mobile_phone: this.paymentData.mobile_phone,
                        mobile_operator: this.paymentData.mobile_operator,
                        company_name: this.paymentData.company_name,
                        company_siret: this.paymentData.company_siret,
                        company_address: this.paymentData.company_address,
                    }),
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    this.paymentSuccess = false;
                    this.paymentErrorMessage = data.message || 'La transaction a été refusée par l\'émetteur ou l\'authentification 3D Secure a échoué.';
                    this.step = 6;
                    return;
                }

                // Succès du paiement
                this.paymentSuccess = true;
                this.subscriptionResult = data.subscription;
                this.step = 6;
            } catch (err) {
                this.paymentSuccess = false;
                this.paymentErrorMessage = 'Une anomalie réseau est survenue lors de la validation bancaire.';
                this.step = 6;
            } finally {
                this.isProcessingPayment = false;
            }
        },

        // Retour en arrière en cas d'échec depuis l'Étape 6 vers l'Étape 5
        retryPayment() {
            this.paymentData.simulate_failure = false; // Réinitialise le simulateur d'échec
            this.step = 5;
        },
    }));
});
</script>
@endsection
