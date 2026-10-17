@extends('layouts.admin')

@section('title', 'Constructeur de Menus & Résolution CTE Récursive')
@section('subtitle', 'Gestion des emplacements de menus, contrainte stricte check_menu_item_target et aperçu CTE en temps réel')

@section('content')
@php
    $parentItemOptions = ['' => '— Niveau 0 (Racine) —'];
    if ($activeMenu) {
        foreach ($activeMenu->items->whereNull('parent_id') as $rootItem) {
            $parentItemOptions[$rootItem->id] = $rootItem->label;
        }
    }

    $menuTypeOptions = [
        'category' => 'Catégorie (Rubrique)',
        'post' => 'Article ou Page Publiée',
        'custom_url' => 'Lien personnalisé (URL)',
        'container' => 'Conteneur de sous-menu (#)',
    ];

    $categoryMenuOptions = ['' => '— Sélectionner une catégorie —'];
    foreach ($categories as $cat) {
        $categoryMenuOptions[$cat->id] = $cat->name . ' (/category/' . $cat->slug . ')';
    }

    $postMenuOptions = ['' => '— Sélectionner un contenu publié —'];
    foreach ($publishedPosts as $p) {
        $postMenuOptions[$p->id] = '[' . strtoupper($p->type->value) . '] ' . $p->title;
    }

    $targetOptions = [
        '_self' => 'Même onglet (_self)',
        '_blank' => 'Nouvel onglet (_blank)',
    ];
@endphp

<div class="space-y-8">

    <!-- Sélection de l'emplacement de menu -->
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-2">
            @foreach($menus as $m)
                <a
                    href="{{ route('admin.menus.index', ['menu_id' => $m->id]) }}"
                    class="px-4 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider transition inline-flex items-center gap-2 {{ $activeMenu && $activeMenu->id === $m->id ? 'bg-red-700 text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-700 hover:border-red-700' }}"
                >
                    <i data-lucide="compass" class="w-3.5 h-3.5"></i>
                    <span>{{ $m->name }}</span>
                    <span class="opacity-75 font-mono">({{ $m->location }})</span>
                </a>
            @endforeach
        </div>
    </div>

    @if($activeMenu)
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

            <!-- Formulaire d'ajout d'un élément de menu (5 colonnes) -->
            <div class="lg:col-span-5 space-y-6">
                <div
                    class="bg-white rounded-2xl border border-slate-200 p-6 shadow-2xs"
                    x-data="{ itemType: 'category' }"
                    @select-change.window="if ($event.detail.name === 'type') itemType = $event.detail.value"
                >
                    <h2 class="text-sm font-black uppercase tracking-wider text-slate-900 mb-4 flex items-center gap-2">
                        <i data-lucide="plus-circle" class="w-4 h-4 text-red-700"></i>
                        <span>Ajouter dans « {{ $activeMenu->name }} »</span>
                    </h2>

                    <form action="{{ route('admin.menus.items.store', $activeMenu) }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Libellé (Label) *</label>
                                <input type="text" name="label" required placeholder="Ex: Grands Dossiers" class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Élément Parent (Sous-menu)</label>
                                <x-ui.select
                                    name="parent_id"
                                    :options="$parentItemOptions"
                                    value=""
                                    placeholder="— Niveau 0 (Racine) —"
                                />
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Type de cible (`menu_item_type`) *</label>
                            <x-ui.select
                                name="type"
                                :options="$menuTypeOptions"
                                value="category"
                                :searchable="false"
                            />
                        </div>

                        <!-- Cible : Catégorie -->
                        <div x-show="itemType === 'category'">
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Choisir la Catégorie *</label>
                            <x-ui.select
                                name="category_id"
                                :options="$categoryMenuOptions"
                                value=""
                                placeholder="Rechercher une catégorie..."
                            />
                        </div>

                        <!-- Cible : Article / Page -->
                        <div x-show="itemType === 'post'" style="display: none;">
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Choisir l'Article ou la Page *</label>
                            <x-ui.select
                                name="post_id"
                                :options="$postMenuOptions"
                                value=""
                                placeholder="Rechercher un article ou une page..."
                            />
                        </div>

                        <!-- Cible : Custom URL -->
                        <div x-show="itemType === 'custom_url'" style="display: none;">
                            <label class="block text-xs font-semibold text-slate-700 mb-1">URL personnalisée *</label>
                            <input type="text" name="custom_url" placeholder="Ex: /search ou https://..." class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Badge</label>
                                <input type="text" name="badge_text" placeholder="Ex: Nouveau" class="w-full px-3 py-2.5 text-sm border border-slate-300 rounded-xl">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Ordre</label>
                                <input type="number" name="display_order" value="10" class="w-full px-3 py-2.5 text-sm border border-slate-300 rounded-xl">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Target</label>
                                <x-ui.select
                                    name="target"
                                    :options="$targetOptions"
                                    value="_self"
                                    :searchable="false"
                                />
                            </div>
                        </div>

                        <button type="submit" class="w-full py-2.5 bg-red-700 hover:bg-red-800 text-white text-xs font-bold uppercase tracking-wider rounded-xl inline-flex items-center justify-center gap-1.5 cursor-pointer">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            <span>Ajouter au Menu</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Aperçu CTE Récursive & Liste des items (7 colonnes) -->
            <div class="lg:col-span-7 space-y-6">
                <div class="bg-slate-950 text-white rounded-2xl p-6 shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-3 mb-4">
                        <div>
                            <h2 class="text-sm font-black uppercase tracking-wider text-amber-400 flex items-center gap-2">
                                <i data-lucide="git-branch" class="w-4 h-4"></i>
                                <span>Résultat Temps Réel de la CTE Récursive (`WITH RECURSIVE menu_tree`)</span>
                            </h2>
                            <p class="text-xs text-slate-400 mt-0.5">
                                Exclut automatiquement les articles dépubliés ou supprimés et calcule `resolved_url` + `depth`.
                            </p>
                        </div>
                        <span class="px-2.5 py-1 text-[10px] font-mono bg-slate-800 text-emerald-400 rounded-md">
                            {{ count($resolvedPreview) }} nœuds actifs
                        </span>
                    </div>

                    <div class="space-y-2 font-mono text-xs">
                        @foreach($resolvedPreview as $node)
                            <div class="flex items-center justify-between py-2.5 px-3.5 rounded-xl bg-slate-900 border border-slate-800" style="margin-left: {{ ($node['depth'] - 1) * 24 }}px">
                                <div class="flex items-center gap-2">
                                    <span class="text-slate-500">Niv.{{ $node['depth'] }}</span>
                                    <span class="font-bold text-white">{{ $node['label'] }}</span>
                                    @if(!empty($node['badge_text']))
                                        <span class="px-1.5 py-0.2 text-[10px] bg-red-700 text-white rounded-xs">{{ $node['badge_text'] }}</span>
                                    @endif
                                    <span class="text-emerald-400">→ {{ $node['resolved_url'] }}</span>
                                </div>

                                <form action="{{ route('admin.menus.items.destroy', $node['id']) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-400 hover:text-rose-300 inline-flex items-center gap-1 cursor-pointer" title="Retirer">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>
    @endif

</div>
@endsection
