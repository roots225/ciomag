@extends('layouts.admin')

@section('title', 'Abonnements Premium & Lecteurs Abonnés')
@section('header_title', 'Gestion des Abonnements')

@section('top_actions')
    <div class="flex items-center gap-2.5">
        <button type="button"
                onclick="window.dispatchEvent(new CustomEvent('open-assign-sub-modal'))"
                class="px-3.5 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 text-xs font-bold uppercase tracking-wider rounded-xl transition-colors inline-flex items-center gap-2 cursor-pointer shadow-2xs">
            <i data-lucide="user-plus" class="w-4 h-4 text-slate-500"></i>
            <span>Attribuer un abonnement</span>
        </button>
        <button type="button"
                onclick="window.dispatchEvent(new CustomEvent('open-create-plan-modal'))"
                class="px-4 py-2 bg-red-700 hover:bg-red-800 text-white text-xs font-bold uppercase tracking-wider rounded-xl transition-colors inline-flex items-center gap-2 shadow-xs cursor-pointer">
            <i data-lucide="plus-circle" class="w-4 h-4"></i>
            <span>Ajouter une formule</span>
        </button>
    </div>
@endsection

@section('content')
@php
    $intervalOptions = [
        'monthly' => 'Mensuel (par mois)',
        'yearly' => 'Annuel (par an)',
        'lifetime' => 'Accès Illimité',
    ];

    $statusOptions = [
        'active' => 'Actif (Accès Premium autorisé)',
        'pending' => 'En attente de validation',
        'cancelled' => 'Désactivé / Résilié',
        'expired' => 'Expiré',
    ];

    $statusFilterOptions = [
        '' => 'Tous les statuts',
        'active' => 'Actifs uniquement',
        'cancelled' => 'Désactivés / Résiliés',
        'expired' => 'Expirés',
        'pending' => 'En attente',
    ];

    $paymentMethodOptions = [
        'visa_stripe' => 'Carte Visa (Stripe)',
        'visa_braintree' => 'Carte Visa (Braintree)',
        'visa' => 'Carte Visa',
        'bank_transfer' => 'Virement bancaire',
        'manual' => 'Attribution manuelle Admin',
    ];

    $planSelectOptions = $plans->mapWithKeys(fn ($p) => [
        $p->id => $p->name . ' (' . $p->formattedPrice() . ' / ' . $p->intervalLabel() . ')',
    ])->all();

    $userSelectOptions = $users->mapWithKeys(fn ($u) => [
        $u->id => $u->display_name . ' (' . $u->email . ')',
    ])->all();
@endphp

<div x-data="{
        createPlanModalOpen: {{ $errors->has('features_text') ? 'true' : 'false' }},
        assignSubModalOpen: false,
        activeEditPlanId: null,
        activeEditSubId: null
     }"
     @open-create-plan-modal.window="createPlanModalOpen = true"
     @open-assign-sub-modal.window="assignSubModalOpen = true"
     class="space-y-8">

    <!-- En-tête KPIs + Bouton Ajouter une formule -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-2xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Formules disponibles</span>
                <div class="text-2xl font-black text-slate-900 mt-1">{{ $plans->count() }}</div>
                <span class="text-[11px] text-emerald-600 font-semibold">{{ $plans->where('is_active', true)->count() }} active(s)</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <i data-lucide="crown" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-2xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Abonnements</span>
                <div class="text-2xl font-black text-slate-900 mt-1">{{ $subscriptions->total() }}</div>
                <span class="text-[11px] text-slate-500">Historique complet</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center">
                <i data-lucide="users" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-2xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Abonnés Actifs</span>
                <div class="text-2xl font-black text-emerald-600 mt-1">{{ \App\Models\UserSubscription::where('status', 'active')->count() }}</div>
                <span class="text-[11px] text-emerald-600 font-semibold">Accès Premium ouvert</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <i data-lucide="shield-check" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-2xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Revenus Abonnements</span>
                <div class="text-2xl font-black text-slate-900 mt-1">
                    {{ number_format((float) \App\Models\UserSubscription::where('status', 'active')->sum('amount_paid'), 2, ',', ' ') }} €
                </div>
                <span class="text-[11px] text-slate-500">Cumul abonnements actifs</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-red-50 text-red-700 flex items-center justify-center">
                <i data-lucide="credit-card" class="w-5 h-5"></i>
            </div>
        </div>
    </div>

    <!-- Section 1 : Formules d'Abonnement Disponibles -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-2xs space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4">
            <div>
                <h2 class="text-base font-black uppercase tracking-wider text-slate-900 flex items-center gap-2">
                    <i data-lucide="crown" class="w-5 h-5 text-amber-500"></i>
                    <span>Formules d'Abonnement Disponibles ({{ $plans->count() }})</span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Créez, modifiez ou activez/désactivez vos offres d'abonnement lecteur.</p>
            </div>
            <button type="button"
                    @click="createPlanModalOpen = true"
                    class="px-4 py-2.5 bg-red-700 hover:bg-red-800 text-white text-xs font-bold uppercase tracking-wider rounded-xl transition-colors inline-flex items-center gap-2 shadow-xs cursor-pointer">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>Ajouter une formule</span>
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse($plans as $plan)
                <div class="rounded-2xl border {{ $plan->is_active ? ($plan->is_featured ? 'border-amber-400 bg-amber-50/20' : 'border-slate-200 bg-white') : 'border-slate-200 bg-slate-50/70 opacity-80' }} p-5 flex flex-col justify-between gap-4 relative transition hover:shadow-md">
                    <div>
                        <div class="flex items-start justify-between gap-2 mb-2">
                            <div class="flex flex-wrap items-center gap-1.5">
                                <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded-md {{ $plan->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600' }}">
                                    {{ $plan->is_active ? 'Active' : 'Inactive' }}
                                </span>
                                @if($plan->badge)
                                    <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded-md bg-amber-100 text-amber-800">
                                        {{ $plan->badge }}
                                    </span>
                                @endif
                                @if($plan->is_featured)
                                    <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded-md bg-red-100 text-red-700">
                                        Mise en avant
                                    </span>
                                @endif
                            </div>
                            <span class="px-2.5 py-0.5 text-[11px] font-bold bg-slate-100 text-slate-700 rounded-full shrink-0">
                                {{ $plan->subscriptions_count }} abonné(s)
                            </span>
                        </div>

                        <h3 class="text-base font-extrabold text-slate-900">{{ $plan->name }}</h3>
                        <div class="text-lg font-black text-red-700 mt-1">
                            {{ $plan->formattedPrice() }}
                            <span class="text-xs font-semibold text-slate-500">/ {{ $plan->intervalLabel() }}</span>
                        </div>

                        @if($plan->description)
                            <p class="text-xs text-slate-500 mt-2 line-clamp-2">{{ $plan->description }}</p>
                        @endif

                        @if(!empty($plan->features))
                            <ul class="mt-3 space-y-1.5 border-t border-slate-100 pt-3">
                                @foreach(array_slice($plan->features, 0, 4) as $feat)
                                    <li class="text-xs text-slate-600 flex items-center gap-1.5">
                                        <i data-lucide="check" class="w-3.5 h-3.5 text-emerald-600 shrink-0"></i>
                                        <span class="truncate">{{ $feat }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                        <button type="button"
                                @click="activeEditPlanId = '{{ $plan->id }}'"
                                class="px-3 py-1.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-lg inline-flex items-center gap-1.5 transition cursor-pointer">
                            <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                            <span>Modifier</span>
                        </button>

                        <div class="flex items-center gap-1.5">
                            <form action="{{ route('admin.subscriptions.plans.toggle', $plan) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        title="{{ $plan->is_active ? 'Désactiver cette formule' : 'Activer cette formule' }}"
                                        class="px-2.5 py-1.5 text-xs font-bold rounded-lg border transition cursor-pointer inline-flex items-center gap-1 {{ $plan->is_active ? 'border-amber-200 bg-amber-50 text-amber-800 hover:bg-amber-100' : 'border-emerald-200 bg-emerald-50 text-emerald-800 hover:bg-emerald-100' }}">
                                    <i data-lucide="{{ $plan->is_active ? 'pause-circle' : 'play-circle' }}" class="w-3.5 h-3.5"></i>
                                    <span>{{ $plan->is_active ? 'Désactiver' : 'Activer' }}</span>
                                </button>
                            </form>

                            <form action="{{ route('admin.subscriptions.plans.destroy', $plan) }}" method="POST" onsubmit="return confirm('Supprimer ou archiver cette formule ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-rose-600 hover:bg-rose-50 rounded-lg cursor-pointer" title="Supprimer">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-8 text-center text-slate-400 text-sm">
                    Aucune formule configurée. Cliquez sur « Ajouter une formule » pour commencer.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Section 2 : Liste & Gestion des Abonnements Utilisateurs -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-2xs">
        <div class="px-6 py-4 border-b border-slate-100 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <h2 class="text-sm font-black uppercase tracking-wider text-slate-900 flex items-center gap-2">
                    <i data-lucide="users" class="w-4 h-4 text-red-700"></i>
                    <span>Abonnements des Utilisateurs ({{ $subscriptions->total() }})</span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Modifiez la formule d'un lecteur, prolongez sa validité ou activez/désactivez son abonnement en un clic.</p>
            </div>

            <form method="GET" action="{{ route('admin.subscriptions.index') }}" class="flex flex-wrap items-center gap-2.5">
                <div class="relative w-60">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                    <input type="text" name="q" value="{{ $search ?? '' }}" placeholder="Nom, email, réf..."
                           class="w-full pl-9 pr-3 py-2 text-xs border border-slate-200 rounded-xl focus:outline-none focus:border-slate-900">
                </div>
                <div class="w-48">
                    <x-ui.select name="status" :options="$statusFilterOptions" :value="$statusFilter ?? ''" :searchable="false" />
                </div>
                <button type="submit" class="px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl cursor-pointer inline-flex items-center gap-1.5">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    <span>Filtrer</span>
                </button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-500">
                        <th class="py-3.5 px-5">Lecteur / Utilisateur</th>
                        <th class="py-3.5 px-4">Formule & Paiement</th>
                        <th class="py-3.5 px-4">Période de validité</th>
                        <th class="py-3.5 px-4">Statut</th>
                        <th class="py-3.5 px-5 text-right">Actions (Activer / Modifier)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($subscriptions as $sub)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-4 px-5 text-xs">
                                <div class="font-bold text-slate-900">{{ $sub->user?->display_name ?? 'Utilisateur supprimé' }}</div>
                                <div class="text-slate-400">{{ $sub->user?->email }}</div>
                            </td>
                            <td class="py-4 px-4 text-xs">
                                <span class="font-bold text-slate-800">{{ $sub->plan?->name ?? 'Formule archivée' }}</span>
                                <div class="text-slate-500 mt-0.5">
                                    <span class="font-semibold text-slate-700">{{ number_format((float) $sub->amount_paid, 2, ',', ' ') }} {{ $sub->currency }}</span>
                                    · <span class="font-mono text-[11px] text-slate-400">{{ $sub->payment_reference }}</span>
                                </div>
                            </td>
                            <td class="py-4 px-4 text-xs text-slate-600">
                                Du <strong>{{ $sub->starts_at?->format('d/m/Y') ?? '—' }}</strong><br>
                                au <strong>{{ $sub->expires_at ? $sub->expires_at->format('d/m/Y') : 'Illimité' }}</strong>
                            </td>
                            <td class="py-4 px-4">
                                @php
                                    $badgeStyle = match($sub->status) {
                                        'active' => 'bg-emerald-100 text-emerald-800',
                                        'pending' => 'bg-amber-100 text-amber-800',
                                        'expired' => 'bg-rose-100 text-rose-800',
                                        default => 'bg-slate-200 text-slate-700',
                                    };
                                    $statusLabel = match($sub->status) {
                                        'active' => 'Actif',
                                        'pending' => 'En attente',
                                        'expired' => 'Expiré',
                                        'cancelled' => 'Désactivé',
                                        default => $sub->status,
                                    };
                                @endphp
                                <span class="px-2.5 py-1 text-[10px] font-bold uppercase rounded-md {{ $badgeStyle }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            <td class="py-4 px-5 text-right">
                                <div class="inline-flex items-center justify-end gap-2">
                                    <!-- Bouton Activer / Désactiver en 1 clic -->
                                    <form action="{{ route('admin.subscriptions.toggle', $sub) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                                class="px-3 py-1.5 text-xs font-bold rounded-lg border transition cursor-pointer inline-flex items-center gap-1.5 {{ $sub->status === 'active' ? 'border-rose-200 bg-rose-50 text-rose-700 hover:bg-rose-100' : 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                                            <i data-lucide="{{ $sub->status === 'active' ? 'user-x' : 'user-check' }}" class="w-3.5 h-3.5"></i>
                                            <span>{{ $sub->status === 'active' ? 'Désactiver' : 'Activer' }}</span>
                                        </button>
                                    </form>

                                    <!-- Bouton Modifier l'abonnement de l'utilisateur -->
                                    <button type="button"
                                            @click="activeEditSubId = '{{ $sub->id }}'"
                                            class="px-3 py-1.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-lg inline-flex items-center gap-1.5 transition cursor-pointer">
                                        <i data-lucide="sliders-horizontal" class="w-3.5 h-3.5"></i>
                                        <span>Modifier</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400 text-xs">
                                Aucun abonnement trouvé pour ces critères.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>
        {{ $subscriptions->links() }}
    </div>

    <!-- ========================================================================= -->
    <!-- DIALOG 1 : CRÉER UNE NOUVELLE FORMULE D'ABONNEMENT                        -->
    <!-- ========================================================================= -->
    <div x-show="createPlanModalOpen"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs"
         @keydown.escape.window="createPlanModalOpen = false">
        <div @click.outside="createPlanModalOpen = false"
             class="bg-white rounded-2xl border border-slate-200 shadow-2xl w-full max-w-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                <h3 class="text-sm font-black uppercase tracking-wider text-slate-900 flex items-center gap-2">
                    <i data-lucide="crown" class="w-4 h-4 text-amber-500"></i>
                    <span>Ajouter une Formule d'Abonnement</span>
                </h3>
                <button type="button" @click="createPlanModalOpen = false" class="p-1.5 text-slate-400 hover:text-slate-700 rounded-lg cursor-pointer">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form action="{{ route('admin.subscriptions.plans.store') }}" method="POST" class="p-6 space-y-4 max-h-[82vh] overflow-y-auto">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nom de l'offre *</label>
                        <input type="text" name="name" required placeholder="Ex: Pass Annuel DSI" class="w-full px-3.5 py-2 text-sm border border-slate-300 rounded-xl">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Badge commercial</label>
                        <input type="text" name="badge" placeholder="Ex: Populaire, Meilleure offre" class="w-full px-3.5 py-2 text-sm border border-slate-300 rounded-xl">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Périodicité *</label>
                        <x-ui.select name="billing_interval" :options="$intervalOptions" value="yearly" :searchable="false" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tarif (€) *</label>
                        <input type="number" step="0.01" name="price" required value="99.00" class="w-full px-3.5 py-2 text-sm font-bold border border-slate-300 rounded-xl">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Ordre d'affichage</label>
                        <input type="number" name="display_order" value="{{ $plans->count() + 1 }}" class="w-full px-3.5 py-2 text-sm border border-slate-300 rounded-xl">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Description courte</label>
                    <textarea name="description" rows="2" placeholder="Résumé de la formule pour la page d'abonnement..." class="w-full px-3.5 py-2 text-sm border border-slate-300 rounded-xl"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Avantages inclus (1 avantage par ligne) *</label>
                    <textarea name="features_text" rows="4" required placeholder="Accès illimité aux articles Premium&#10;6 numéros PDF HD inclus&#10;Invitation VIP aux forums CIOMAG" class="w-full px-3.5 py-2 text-xs border border-slate-300 rounded-xl"></textarea>
                </div>

                <div class="space-y-2 pt-1">
                    <x-ui.checkbox name="is_active" value="1" :checked="true" label="Formule active et visible publiquement" />
                    <x-ui.checkbox name="is_featured" value="1" :checked="false" label="Formule mise en avant (Recommandée)" />
                    <x-ui.checkbox name="includes_print" value="1" :checked="true" label="Inclut les éditions du magazine" />
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                    <button type="button" @click="createPlanModalOpen = false" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl cursor-pointer">
                        Annuler
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-red-700 hover:bg-red-800 text-white text-xs font-bold uppercase tracking-wider rounded-xl inline-flex items-center gap-1.5 cursor-pointer">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Créer la formule</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- DIALOGS 2 : MODIFIER CHACUNE DES FORMULES DISPONIBLES                     -->
    <!-- ========================================================================= -->
    @foreach($plans as $plan)
        <div x-show="activeEditPlanId === '{{ $plan->id }}'"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs"
             @keydown.escape.window="activeEditPlanId = null">
            <div @click.outside="activeEditPlanId = null"
                 class="bg-white rounded-2xl border border-slate-200 shadow-2xl w-full max-w-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                    <h3 class="text-sm font-black uppercase tracking-wider text-slate-900 flex items-center gap-2">
                        <i data-lucide="pencil" class="w-4 h-4 text-red-700"></i>
                        <span>Modifier la formule : {{ $plan->name }}</span>
                    </h3>
                    <button type="button" @click="activeEditPlanId = null" class="p-1.5 text-slate-400 hover:text-slate-700 rounded-lg cursor-pointer">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form action="{{ route('admin.subscriptions.plans.update', $plan) }}" method="POST" class="p-6 space-y-4 max-h-[82vh] overflow-y-auto">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Nom de l'offre *</label>
                            <input type="text" name="name" required value="{{ $plan->name }}" class="w-full px-3.5 py-2 text-sm border border-slate-300 rounded-xl">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Badge commercial</label>
                            <input type="text" name="badge" value="{{ $plan->badge }}" class="w-full px-3.5 py-2 text-sm border border-slate-300 rounded-xl">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Périodicité *</label>
                            <x-ui.select name="billing_interval" :options="$intervalOptions" :value="$plan->billing_interval" :searchable="false" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Tarif (€) *</label>
                            <input type="number" step="0.01" name="price" required value="{{ $plan->price }}" class="w-full px-3.5 py-2 text-sm font-bold border border-slate-300 rounded-xl">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Ordre d'affichage</label>
                            <input type="number" name="display_order" value="{{ $plan->display_order }}" class="w-full px-3.5 py-2 text-sm border border-slate-300 rounded-xl">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Description courte</label>
                        <textarea name="description" rows="2" class="w-full px-3.5 py-2 text-sm border border-slate-300 rounded-xl">{{ $plan->description }}</textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Avantages inclus (1 par ligne) *</label>
                        <textarea name="features_text" rows="4" required class="w-full px-3.5 py-2 text-xs border border-slate-300 rounded-xl">{{ implode("\n", $plan->features ?? []) }}</textarea>
                    </div>

                    <div class="space-y-2 pt-1">
                        <x-ui.checkbox name="is_active" value="1" :checked="(bool) $plan->is_active" label="Formule active (disponible à la souscription)" />
                        <x-ui.checkbox name="is_featured" value="1" :checked="(bool) $plan->is_featured" label="Formule mise en avant (Recommandée)" />
                        <x-ui.checkbox name="includes_print" value="1" :checked="(bool) $plan->includes_print" label="Inclut les éditions du magazine" />
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                        <button type="button" @click="activeEditPlanId = null" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl cursor-pointer">
                            Annuler
                        </button>
                        <button type="submit" class="px-5 py-2.5 bg-slate-900 hover:bg-red-700 text-white text-xs font-bold uppercase tracking-wider rounded-xl inline-flex items-center gap-1.5 cursor-pointer">
                            <i data-lucide="save" class="w-4 h-4"></i>
                            <span>Enregistrer les modifications</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

    <!-- ========================================================================= -->
    <!-- DIALOGS 3 : MODIFIER / ACTIVER / DÉSACTIVER L'ABONNEMENT D'UN UTILISATEUR -->
    <!-- ========================================================================= -->
    @foreach($subscriptions as $sub)
        <div x-show="activeEditSubId === '{{ $sub->id }}'"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs"
             @keydown.escape.window="activeEditSubId = null">
            <div @click.outside="activeEditSubId = null"
                 class="bg-white rounded-2xl border border-slate-200 shadow-2xl w-full max-w-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                    <div>
                        <h3 class="text-sm font-black uppercase tracking-wider text-slate-900">
                            Modifier l'abonnement lecteur
                        </h3>
                        <p class="text-xs text-slate-500">{{ $sub->user?->display_name }} ({{ $sub->user?->email }})</p>
                    </div>
                    <button type="button" @click="activeEditSubId = null" class="p-1.5 text-slate-400 hover:text-slate-700 rounded-lg cursor-pointer">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form action="{{ route('admin.subscriptions.update', $sub) }}" method="POST" class="p-6 space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Formule d'abonnement *</label>
                        <x-ui.select name="plan_id" :options="$planSelectOptions" :value="$sub->plan_id" :searchable="false" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Statut de l'abonnement *</label>
                        <x-ui.select name="status" :options="$statusOptions" :value="$sub->status" :searchable="false" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Montant réglé (€) *</label>
                            <input type="number" step="0.01" name="amount_paid" required value="{{ $sub->amount_paid }}" class="w-full px-3.5 py-2 text-sm font-bold border border-slate-300 rounded-xl">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Mode de paiement</label>
                            <x-ui.select name="payment_method" :options="$paymentMethodOptions" :value="$sub->payment_method ?? 'visa'" :searchable="false" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Date de début *</label>
                            <input type="date" name="starts_at" required value="{{ $sub->starts_at?->format('Y-m-d') }}" class="w-full px-3.5 py-2 text-sm border border-slate-300 rounded-xl">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Date d'expiration (vide = illimité)</label>
                            <input type="date" name="expires_at" value="{{ $sub->expires_at?->format('Y-m-d') }}" class="w-full px-3.5 py-2 text-sm border border-slate-300 rounded-xl">
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                        <button type="button" @click="activeEditSubId = null" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl cursor-pointer">
                            Annuler
                        </button>
                        <button type="submit" class="px-5 py-2.5 bg-slate-900 hover:bg-red-700 text-white text-xs font-bold uppercase tracking-wider rounded-xl inline-flex items-center gap-1.5 cursor-pointer">
                            <i data-lucide="save" class="w-4 h-4"></i>
                            <span>Mettre à jour l'abonnement</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

    <!-- ========================================================================= -->
    <!-- DIALOG 4 : ATTRIBUER UN ABONNEMENT À UN UTILISATEUR                       -->
    <!-- ========================================================================= -->
    <div x-show="assignSubModalOpen"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs"
         @keydown.escape.window="assignSubModalOpen = false">
        <div @click.outside="assignSubModalOpen = false"
             class="bg-white rounded-2xl border border-slate-200 shadow-2xl w-full max-w-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                <h3 class="text-sm font-black uppercase tracking-wider text-slate-900 flex items-center gap-2">
                    <i data-lucide="user-plus" class="w-4 h-4 text-red-700"></i>
                    <span>Attribuer un Abonnement Utilisateur</span>
                </h3>
                <button type="button" @click="assignSubModalOpen = false" class="p-1.5 text-slate-400 hover:text-slate-700 rounded-lg cursor-pointer">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form action="{{ route('admin.subscriptions.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Sélectionner l'utilisateur *</label>
                    <x-ui.select name="user_id" :options="$userSelectOptions" :value="array_key_first($userSelectOptions)" :searchable="true" />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Formule d'abonnement *</label>
                    <x-ui.select name="plan_id" :options="$planSelectOptions" :value="array_key_first($planSelectOptions)" :searchable="false" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Statut initial *</label>
                        <x-ui.select name="status" :options="$statusOptions" value="active" :searchable="false" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Mode de paiement</label>
                        <x-ui.select name="payment_method" :options="$paymentMethodOptions" value="visa_stripe" :searchable="false" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Date de début</label>
                        <input type="date" name="starts_at" value="{{ now()->format('Y-m-d') }}" class="w-full px-3.5 py-2 text-sm border border-slate-300 rounded-xl">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Date d'expiration</label>
                        <input type="date" name="expires_at" value="{{ now()->addYear()->format('Y-m-d') }}" class="w-full px-3.5 py-2 text-sm border border-slate-300 rounded-xl">
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                    <button type="button" @click="assignSubModalOpen = false" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl cursor-pointer">
                        Annuler
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-red-700 hover:bg-red-800 text-white text-xs font-bold uppercase tracking-wider rounded-xl inline-flex items-center gap-1.5 cursor-pointer">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Activer l'abonnement</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
