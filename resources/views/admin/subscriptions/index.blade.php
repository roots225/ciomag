@extends('layouts.admin')

@section('title', 'Abonnements Premium & Lecteurs Abonnés')
@section('header_title', 'Gestion des Abonnements')

@section('top_actions')
    <div class="flex items-center gap-2">
        <button type="button"
                onclick="window.dispatchEvent(new CustomEvent('open-assign-sub-modal'))"
                class="btn btn-sm btn-secondary">
            <i data-lucide="user-plus" class="w-4 h-4 text-slate-500"></i>
            <span>Attribuer un abonnement</span>
        </button>
        <button type="button"
                onclick="window.dispatchEvent(new CustomEvent('open-create-plan-modal'))"
                class="btn btn-sm btn-primary">
            <i data-lucide="plus" class="w-4 h-4"></i>
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
     class="space-y-6">

    <!-- En-tête KPIs -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="admin-card p-4 flex items-center justify-between">
            <div>
                <span class="text-xs font-medium text-slate-500">Formules disponibles</span>
                <div class="text-xl font-bold text-slate-900 mt-0.5">{{ $plans->count() }}</div>
                <span class="text-[11px] text-emerald-600 font-medium">{{ $plans->where('is_active', true)->count() }} active(s)</span>
            </div>
            <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                <i data-lucide="crown" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="admin-card p-4 flex items-center justify-between">
            <div>
                <span class="text-xs font-medium text-slate-500">Total Abonnements</span>
                <div class="text-xl font-bold text-slate-900 mt-0.5">{{ $subscriptions->total() }}</div>
                <span class="text-[11px] text-slate-400">Historique complet</span>
            </div>
            <div class="w-10 h-10 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center">
                <i data-lucide="users" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="admin-card p-4 flex items-center justify-between">
            <div>
                <span class="text-xs font-medium text-slate-500">Abonnés Actifs</span>
                <div class="text-xl font-bold text-emerald-600 mt-0.5">{{ \App\Models\UserSubscription::where('status', 'active')->count() }}</div>
                <span class="text-[11px] text-emerald-600 font-medium">Accès Premium ouvert</span>
            </div>
            <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <i data-lucide="shield-check" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="admin-card p-4 flex items-center justify-between">
            <div>
                <span class="text-xs font-medium text-slate-500">Revenus Abonnements</span>
                <div class="text-xl font-bold text-slate-900 mt-0.5">
                    {{ number_format((float) \App\Models\UserSubscription::where('status', 'active')->sum('amount_paid'), 2, ',', ' ') }} €
                </div>
                <span class="text-[11px] text-slate-400">Cumul abonnements actifs</span>
            </div>
            <div class="w-10 h-10 rounded-lg bg-red-50 text-red-700 flex items-center justify-center">
                <i data-lucide="credit-card" class="w-5 h-5"></i>
            </div>
        </div>
    </div>

    <!-- Section 1 : Formules d'Abonnement Disponibles -->
    <div class="admin-card p-5 sm:p-6 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
            <div>
                <h2 class="text-sm font-semibold text-slate-900 flex items-center gap-2">
                    <i data-lucide="crown" class="w-4 h-4 text-amber-500"></i>
                    <span>Formules d'Abonnement ({{ $plans->count() }})</span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Créez, modifiez ou activez/désactivez vos offres d'abonnement lecteur.</p>
            </div>
            <button type="button"
                    @click="createPlanModalOpen = true"
                    class="btn btn-sm btn-primary self-start sm:self-auto">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Ajouter une formule</span>
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($plans as $plan)
                <div class="rounded-xl border {{ $plan->is_active ? ($plan->is_featured ? 'border-amber-300 bg-amber-50/20' : 'border-slate-200 bg-white') : 'border-slate-200 bg-slate-50/70 opacity-80' }} p-4 flex flex-col justify-between gap-3.5 relative transition hover:shadow-xs">
                    <div>
                        <div class="flex items-start justify-between gap-2 mb-2">
                            <div class="flex flex-wrap items-center gap-1.5">
                                <span class="admin-badge {{ $plan->is_active ? 'badge-success' : 'badge-neutral' }}">
                                    {{ $plan->is_active ? 'Active' : 'Inactive' }}
                                </span>
                                @if($plan->badge)
                                    <span class="admin-badge badge-warning">
                                        {{ $plan->badge }}
                                    </span>
                                @endif
                                @if($plan->is_featured)
                                    <span class="admin-badge badge-primary">
                                        Mise en avant
                                    </span>
                                @endif
                            </div>
                            <span class="text-[11px] font-medium text-slate-500 bg-slate-100 px-2 py-0.5 rounded shrink-0">
                                {{ $plan->subscriptions_count }} abonné(s)
                            </span>
                        </div>

                        <h3 class="text-sm font-bold text-slate-900">{{ $plan->name }}</h3>
                        <div class="text-base font-extrabold text-red-700 mt-0.5">
                            {{ $plan->formattedPrice() }}
                            <span class="text-xs font-normal text-slate-500">/ {{ $plan->intervalLabel() }}</span>
                        </div>

                        @if($plan->description)
                            <p class="text-xs text-slate-500 mt-1.5 line-clamp-2">{{ $plan->description }}</p>
                        @endif

                        @if(!empty($plan->features))
                            <ul class="mt-2.5 space-y-1 border-t border-slate-100 pt-2.5">
                                @foreach(array_slice($plan->features, 0, 4) as $feat)
                                    <li class="text-xs text-slate-600 flex items-center gap-1.5">
                                        <i data-lucide="check" class="w-3.5 h-3.5 text-emerald-600 shrink-0"></i>
                                        <span class="truncate">{{ $feat }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>

                    <div class="pt-2.5 border-t border-slate-100 flex items-center justify-between gap-2">
                        <button type="button"
                                @click="activeEditPlanId = '{{ $plan->id }}'"
                                class="btn btn-sm btn-secondary">
                            <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                            <span>Modifier</span>
                        </button>

                        <div class="flex items-center gap-1.5">
                            <form action="{{ route('admin.subscriptions.plans.toggle', $plan) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        title="{{ $plan->is_active ? 'Désactiver cette formule' : 'Activer cette formule' }}"
                                        class="btn btn-sm {{ $plan->is_active ? 'btn-ghost text-amber-700 hover:bg-amber-50' : 'btn-ghost text-emerald-700 hover:bg-emerald-50' }}">
                                    <i data-lucide="{{ $plan->is_active ? 'pause' : 'play' }}" class="w-3.5 h-3.5"></i>
                                    <span>{{ $plan->is_active ? 'Désactiver' : 'Activer' }}</span>
                                </button>
                            </form>

                            <form action="{{ route('admin.subscriptions.plans.destroy', $plan) }}" method="POST" onsubmit="return confirm('Supprimer cette formule ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-rose-600 hover:bg-rose-50 rounded-lg cursor-pointer transition" title="Supprimer">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-8 text-center text-slate-400 text-xs">
                    Aucune formule configurée. Cliquez sur « Ajouter une formule » pour commencer.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Section 2 : Liste & Gestion des Abonnements Utilisateurs -->
    <div class="admin-table-container">
        <div class="px-5 py-3.5 border-b border-slate-100 flex flex-col lg:flex-row lg:items-center justify-between gap-3">
            <div>
                <h2 class="text-sm font-semibold text-slate-900 flex items-center gap-2">
                    <i data-lucide="users" class="w-4 h-4 text-red-700"></i>
                    <span>Abonnements des Utilisateurs ({{ $subscriptions->total() }})</span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Modifiez la formule d'un lecteur, prolongez sa validité ou activez/désactivez son statut.</p>
            </div>

            <form method="GET" action="{{ route('admin.subscriptions.index') }}" class="flex flex-wrap items-center gap-2">
                <div class="relative w-56">
                    <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                    <input type="text" name="q" value="{{ $search ?? '' }}" placeholder="Nom, email, réf..."
                           class="w-full pl-8 pr-3 py-1.5 text-xs border border-slate-200 rounded-lg focus:outline-none focus:border-slate-800">
                </div>
                <div class="w-44">
                    <x-ui.select name="status" :options="$statusFilterOptions" :value="$statusFilter ?? ''" :searchable="false" />
                </div>
                <button type="submit" class="btn btn-sm btn-dark">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    <span>Filtrer</span>
                </button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Lecteur / Utilisateur</th>
                        <th>Formule & Paiement</th>
                        <th>Période de validité</th>
                        <th>Statut</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($subscriptions as $sub)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="text-xs">
                                <div class="font-semibold text-slate-900">{{ $sub->user?->display_name ?? 'Utilisateur supprimé' }}</div>
                                <div class="text-slate-400">{{ $sub->user?->email }}</div>
                            </td>
                            <td class="text-xs">
                                <span class="font-semibold text-slate-800">{{ $sub->plan?->name ?? 'Formule archivée' }}</span>
                                <div class="text-slate-500 mt-0.5">
                                    <span class="font-medium text-slate-700">{{ number_format((float) $sub->amount_paid, 2, ',', ' ') }} {{ $sub->currency }}</span>
                                    · <span class="font-mono text-[11px] text-slate-400">{{ $sub->payment_reference }}</span>
                                </div>
                            </td>
                            <td class="text-xs text-slate-600">
                                Du <span class="font-medium text-slate-800">{{ $sub->starts_at?->format('d/m/Y') ?? '—' }}</span><br>
                                au <span class="font-medium text-slate-800">{{ $sub->expires_at ? $sub->expires_at->format('d/m/Y') : 'Illimité' }}</span>
                            </td>
                            <td>
                                @php
                                    $badgeClass = match($sub->status) {
                                        'active' => 'badge-success',
                                        'pending' => 'badge-warning',
                                        'expired' => 'badge-danger',
                                        default => 'badge-neutral',
                                    };
                                    $statusLabel = match($sub->status) {
                                        'active' => 'Actif',
                                        'pending' => 'En attente',
                                        'expired' => 'Expiré',
                                        'cancelled' => 'Désactivé',
                                        default => $sub->status,
                                    };
                                @endphp
                                <span class="admin-badge {{ $badgeClass }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            <td class="text-right">
                                <div class="inline-flex items-center justify-end gap-1.5">
                                    <!-- Bouton Activer / Désactiver -->
                                    <form action="{{ route('admin.subscriptions.toggle', $sub) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                                class="btn btn-sm {{ $sub->status === 'active' ? 'btn-ghost text-rose-700 hover:bg-rose-50' : 'btn-ghost text-emerald-700 hover:bg-emerald-50' }}">
                                            <i data-lucide="{{ $sub->status === 'active' ? 'user-x' : 'user-check' }}" class="w-3.5 h-3.5"></i>
                                            <span>{{ $sub->status === 'active' ? 'Désactiver' : 'Activer' }}</span>
                                        </button>
                                    </form>

                                    <!-- Bouton Modifier l'abonnement -->
                                    <button type="button"
                                            @click="activeEditSubId = '{{ $sub->id }}'"
                                            class="btn btn-sm btn-secondary">
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

    <!-- DIALOG 1 : CRÉER UNE NOUVELLE FORMULE D'ABONNEMENT -->
    <div x-show="createPlanModalOpen"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/40 backdrop-blur-xs"
         @keydown.escape.window="createPlanModalOpen = false">
        <div @click.outside="createPlanModalOpen = false"
             class="bg-white rounded-xl border border-slate-200 shadow-xl w-full max-w-xl overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <h3 class="text-sm font-semibold text-slate-900 flex items-center gap-2">
                    <i data-lucide="crown" class="w-4 h-4 text-amber-500"></i>
                    <span>Ajouter une Formule d'Abonnement</span>
                </h3>
                <button type="button" @click="createPlanModalOpen = false" class="p-1 text-slate-400 hover:text-slate-600 rounded cursor-pointer">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form action="{{ route('admin.subscriptions.plans.store') }}" method="POST" class="p-5 space-y-4 max-h-[82vh] overflow-y-auto">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="admin-label">Nom de l'offre *</label>
                        <input type="text" name="name" required placeholder="Ex: Pass Annuel DSI" class="admin-input">
                    </div>
                    <div>
                        <label class="admin-label">Badge commercial</label>
                        <input type="text" name="badge" placeholder="Ex: Populaire, Meilleure offre" class="admin-input">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="admin-label">Périodicité *</label>
                        <x-ui.select name="billing_interval" :options="$intervalOptions" value="yearly" :searchable="false" />
                    </div>
                    <div>
                        <label class="admin-label">Tarif (€) *</label>
                        <input type="number" step="0.01" name="price" required value="99.00" class="admin-input font-semibold">
                    </div>
                    <div>
                        <label class="admin-label">Ordre d'affichage</label>
                        <input type="number" name="display_order" value="{{ $plans->count() + 1 }}" class="admin-input">
                    </div>
                </div>

                <div>
                    <label class="admin-label">Description courte</label>
                    <textarea name="description" rows="2" placeholder="Résumé de la formule pour la page d'abonnement..." class="admin-input"></textarea>
                </div>

                <div>
                    <label class="admin-label">Avantages inclus (1 avantage par ligne) *</label>
                    <textarea name="features_text" rows="4" required placeholder="Accès illimité aux articles Premium&#10;6 numéros PDF HD inclus&#10;Invitation VIP aux forums CIOMAG" class="admin-input text-xs"></textarea>
                </div>

                <div class="space-y-2 pt-1">
                    <x-ui.checkbox name="is_active" value="1" :checked="true" label="Formule active et visible publiquement" />
                    <x-ui.checkbox name="is_featured" value="1" :checked="false" label="Formule mise en avant (Recommandée)" />
                    <x-ui.checkbox name="includes_print" value="1" :checked="true" label="Inclut les éditions du magazine" />
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="createPlanModalOpen = false" class="btn btn-sm btn-ghost">
                        Annuler
                    </button>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Créer la formule</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- DIALOGS 2 : MODIFIER CHACUNE DES FORMULES DISPONIBLES -->
    @foreach($plans as $plan)
        <div x-show="activeEditPlanId === '{{ $plan->id }}'"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/40 backdrop-blur-xs"
             @keydown.escape.window="activeEditPlanId = null">
            <div @click.outside="activeEditPlanId = null"
                 class="bg-white rounded-xl border border-slate-200 shadow-xl w-full max-w-xl overflow-hidden">
                <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <h3 class="text-sm font-semibold text-slate-900 flex items-center gap-2">
                        <i data-lucide="pencil" class="w-4 h-4 text-red-700"></i>
                        <span>Modifier la formule : {{ $plan->name }}</span>
                    </h3>
                    <button type="button" @click="activeEditPlanId = null" class="p-1 text-slate-400 hover:text-slate-600 rounded cursor-pointer">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form action="{{ route('admin.subscriptions.plans.update', $plan) }}" method="POST" class="p-5 space-y-4 max-h-[82vh] overflow-y-auto">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="admin-label">Nom de l'offre *</label>
                            <input type="text" name="name" required value="{{ $plan->name }}" class="admin-input">
                        </div>
                        <div>
                            <label class="admin-label">Badge commercial</label>
                            <input type="text" name="badge" value="{{ $plan->badge }}" class="admin-input">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="admin-label">Périodicité *</label>
                            <x-ui.select name="billing_interval" :options="$intervalOptions" :value="$plan->billing_interval" :searchable="false" />
                        </div>
                        <div>
                            <label class="admin-label">Tarif (€) *</label>
                            <input type="number" step="0.01" name="price" required value="{{ $plan->price }}" class="admin-input font-semibold">
                        </div>
                        <div>
                            <label class="admin-label">Ordre d'affichage</label>
                            <input type="number" name="display_order" value="{{ $plan->display_order }}" class="admin-input">
                        </div>
                    </div>

                    <div>
                        <label class="admin-label">Description courte</label>
                        <textarea name="description" rows="2" class="admin-input">{{ $plan->description }}</textarea>
                    </div>

                    <div>
                        <label class="admin-label">Avantages inclus (1 par ligne) *</label>
                        <textarea name="features_text" rows="4" required class="admin-input text-xs">{{ implode("\n", $plan->features ?? []) }}</textarea>
                    </div>

                    <div class="space-y-2 pt-1">
                        <x-ui.checkbox name="is_active" value="1" :checked="(bool) $plan->is_active" label="Formule active (disponible à la souscription)" />
                        <x-ui.checkbox name="is_featured" value="1" :checked="(bool) $plan->is_featured" label="Formule mise en avant (Recommandée)" />
                        <x-ui.checkbox name="includes_print" value="1" :checked="(bool) $plan->includes_print" label="Inclut les éditions du magazine" />
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                        <button type="button" @click="activeEditPlanId = null" class="btn btn-sm btn-ghost">
                            Annuler
                        </button>
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i data-lucide="save" class="w-4 h-4"></i>
                            <span>Enregistrer les modifications</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

    <!-- DIALOGS 3 : MODIFIER L'ABONNEMENT D'UN UTILISATEUR -->
    @foreach($subscriptions as $sub)
        <div x-show="activeEditSubId === '{{ $sub->id }}'"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/40 backdrop-blur-xs"
             @keydown.escape.window="activeEditSubId = null">
            <div @click.outside="activeEditSubId = null"
                 class="bg-white rounded-xl border border-slate-200 shadow-xl w-full max-w-lg overflow-hidden">
                <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900">
                            Modifier l'abonnement lecteur
                        </h3>
                        <p class="text-xs text-slate-500">{{ $sub->user?->display_name }} ({{ $sub->user?->email }})</p>
                    </div>
                    <button type="button" @click="activeEditSubId = null" class="p-1 text-slate-400 hover:text-slate-600 rounded cursor-pointer">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form action="{{ route('admin.subscriptions.update', $sub) }}" method="POST" class="p-5 space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="admin-label">Formule d'abonnement *</label>
                        <x-ui.select name="plan_id" :options="$planSelectOptions" :value="$sub->plan_id" :searchable="false" />
                    </div>

                    <div>
                        <label class="admin-label">Statut de l'abonnement *</label>
                        <x-ui.select name="status" :options="$statusOptions" :value="$sub->status" :searchable="false" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="admin-label">Montant réglé (€) *</label>
                            <input type="number" step="0.01" name="amount_paid" required value="{{ $sub->amount_paid }}" class="admin-input font-semibold">
                        </div>
                        <div>
                            <label class="admin-label">Mode de paiement</label>
                            <x-ui.select name="payment_method" :options="$paymentMethodOptions" :value="$sub->payment_method ?? 'visa'" :searchable="false" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="admin-label">Date de début *</label>
                            <input type="date" name="starts_at" required value="{{ $sub->starts_at?->format('Y-m-d') }}" class="admin-input">
                        </div>
                        <div>
                            <label class="admin-label">Date d'expiration</label>
                            <input type="date" name="expires_at" value="{{ $sub->expires_at?->format('Y-m-d') }}" class="admin-input">
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                        <button type="button" @click="activeEditSubId = null" class="btn btn-sm btn-ghost">
                            Annuler
                        </button>
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i data-lucide="save" class="w-4 h-4"></i>
                            <span>Mettre à jour l'abonnement</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

    <!-- DIALOG 4 : ATTRIBUER UN ABONNEMENT À UN UTILISATEUR -->
    <div x-show="assignSubModalOpen"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/40 backdrop-blur-xs"
         @keydown.escape.window="assignSubModalOpen = false">
        <div @click.outside="assignSubModalOpen = false"
             class="bg-white rounded-xl border border-slate-200 shadow-xl w-full max-w-lg overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <h3 class="text-sm font-semibold text-slate-900 flex items-center gap-2">
                    <i data-lucide="user-plus" class="w-4 h-4 text-red-700"></i>
                    <span>Attribuer un Abonnement Utilisateur</span>
                </h3>
                <button type="button" @click="assignSubModalOpen = false" class="p-1 text-slate-400 hover:text-slate-600 rounded cursor-pointer">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form action="{{ route('admin.subscriptions.store') }}" method="POST" class="p-5 space-y-4">
                @csrf
                <div>
                    <label class="admin-label">Sélectionner l'utilisateur *</label>
                    <x-ui.select name="user_id" :options="$userSelectOptions" :value="array_key_first($userSelectOptions)" :searchable="true" />
                </div>

                <div>
                    <label class="admin-label">Formule d'abonnement *</label>
                    <x-ui.select name="plan_id" :options="$planSelectOptions" :value="array_key_first($planSelectOptions)" :searchable="false" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="admin-label">Statut initial *</label>
                        <x-ui.select name="status" :options="$statusOptions" value="active" :searchable="false" />
                    </div>
                    <div>
                        <label class="admin-label">Mode de paiement</label>
                        <x-ui.select name="payment_method" :options="$paymentMethodOptions" value="visa_stripe" :searchable="false" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="admin-label">Date de début</label>
                        <input type="date" name="starts_at" value="{{ now()->format('Y-m-d') }}" class="admin-input">
                    </div>
                    <div>
                        <label class="admin-label">Date d'expiration</label>
                        <input type="date" name="expires_at" value="{{ now()->addYear()->format('Y-m-d') }}" class="admin-input">
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="assignSubModalOpen = false" class="btn btn-sm btn-ghost">
                        Annuler
                    </button>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Activer l'abonnement</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
