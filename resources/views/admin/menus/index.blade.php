@extends('layouts.admin')

@section('title', 'Constructeur de Menus & Résolution CTE Récursive')
@section('subtitle', 'Gestion des emplacements de menus, organisation hiérarchique par glisser-déposer et aperçu temps réel')

@section('top_actions')
    @if($activeMenu)
        <div class="flex items-center gap-2">
            <button type="button"
                    @click="window.dispatchEvent(new CustomEvent('open-edit-menu-modal'))"
                    class="btn btn-sm btn-secondary cursor-pointer">
                <i data-lucide="settings-2" class="w-4 h-4 text-slate-500"></i>
                <span>Paramètres du Menu</span>
            </button>
            <button type="button"
                    @click="window.dispatchEvent(new CustomEvent('open-create-item-modal'))"
                    class="btn btn-sm btn-primary cursor-pointer">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Ajouter un élément</span>
            </button>
        </div>
    @endif
@endsection

@section('content')
@php
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

    $buildParentOptions = function ($currentItem = null) use ($activeMenu) {
        $options = ['' => '— Niveau 0 (Racine) —'];
        if (!$activeMenu) {
            return $options;
        }

        $excludedIds = [];
        if ($currentItem) {
            $excludedIds[] = $currentItem->id;
            $findDescendants = function ($parentId) use (&$findDescendants, &$excludedIds, $activeMenu) {
                foreach ($activeMenu->items->where('parent_id', $parentId) as $child) {
                    $excludedIds[] = $child->id;
                    $findDescendants($child->id);
                }
            };
            $findDescendants($currentItem->id);
        }

        foreach ($activeMenu->items as $it) {
            if (!in_array($it->id, $excludedIds, true)) {
                $options[$it->id] = ($it->parent ? '— ' . $it->parent->label . ' › ' : '') . $it->label;
            }
        }
        return $options;
    };

    $createParentOptions = $buildParentOptions(null);
@endphp

<div
    class="space-y-6"
    x-data="adminMenuManager({
        activeMenu: {{ Js::from($activeMenu) }},
        initialTree: {{ Js::from($resolvedPreview) }},
        csrfToken: '{{ csrf_token() }}',
        reparentUrlTemplate: '{{ route('admin.menus.items.reparent', ':id') }}',
        sessionSuccess: '{{ session('success') }}',
        sessionError: '{{ session('error') }}',
        hasErrors: {{ $errors->any() ? 'true' : 'false' }},
    })"
    @open-create-item-modal.window="createItemModalOpen = true"
    @open-edit-menu-modal.window="editMenuModalOpen = true"
    @open-edit-item-modal.window="activeEditItemId = $event.detail.id"
    @select-change.window="handleSelectChange($event.detail)"
>

    <!-- SYSTÈME DE TOAST FLOTTANT (Notifications en temps réel) -->
    <div class="fixed top-5 right-5 z-50 flex flex-col gap-2.5 pointer-events-none max-w-sm w-full">
        <template x-for="t in toasts" :key="t.id">
            <div
                x-show="t.visible"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                class="pointer-events-auto p-4 rounded-xl shadow-xl border flex items-start gap-3 bg-white/95 backdrop-blur-md transition"
                :class="t.type === 'success' ? 'border-emerald-200 text-slate-900 shadow-emerald-500/5' : 'border-rose-200 text-slate-900 shadow-rose-500/5'"
            >
                <div class="shrink-0 mt-0.5">
                    <template x-if="t.type === 'success'">
                        <div class="w-6 h-6 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                        </div>
                    </template>
                    <template x-if="t.type === 'error'">
                        <div class="w-6 h-6 rounded-full bg-rose-50 text-rose-600 flex items-center justify-center">
                            <i data-lucide="alert-circle" class="w-4 h-4"></i>
                        </div>
                    </template>
                </div>
                <div class="flex-1 min-w-0">
                    <h4 class="text-xs font-bold leading-tight text-slate-900" x-text="t.title"></h4>
                    <p class="text-xs text-slate-500 mt-0.5 leading-snug" x-text="t.message"></p>
                </div>
                <button
                    type="button"
                    @click="removeToast(t.id)"
                    class="text-slate-400 hover:text-slate-600 p-1 rounded-lg transition cursor-pointer shrink-0"
                >
                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                </button>
            </div>
        </template>
    </div>

    <!-- Navigation entre les emplacements de menu & Bouton Modifier Menu -->
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-1.5">
            @foreach($menus as $m)
                <a
                    href="{{ route('admin.menus.index', ['menu_id' => $m->id]) }}"
                    class="px-3.5 py-1.5 rounded-lg text-xs font-medium transition inline-flex items-center gap-1.5 {{ $activeMenu && $activeMenu->id === $m->id ? 'bg-red-700 text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-50' }}"
                >
                    <i data-lucide="compass" class="w-3.5 h-3.5"></i>
                    <span>{{ $m->name }}</span>
                    <span class="opacity-75 font-mono text-[10px]">({{ $m->location }})</span>
                </a>
            @endforeach
        </div>

        @if($activeMenu)
            <div class="flex items-center gap-2">
                <button
                    type="button"
                    @click="editMenuModalOpen = true"
                    class="btn btn-sm btn-secondary inline-flex items-center gap-1.5 cursor-pointer"
                    title="Modifier le nom ou la description de ce menu"
                >
                    <i data-lucide="edit-3" class="w-3.5 h-3.5 text-slate-500"></i>
                    <span>Modifier ce menu</span>
                </button>

                <button
                    type="button"
                    @click="createItemModalOpen = true"
                    class="btn btn-sm btn-primary inline-flex items-center gap-1.5 cursor-pointer"
                >
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    <span>Ajouter un élément</span>
                </button>
            </div>
        @endif
    </div>

    @if($activeMenu)
        <!-- Aperçu CTE Récursive & Arborescence Interactive Glisser-Déposer -->
        <div class="space-y-6">
            <div class="admin-card p-5 sm:p-6 bg-white border border-slate-200/80 shadow-xs">
                <div class="flex flex-wrap items-center justify-between border-b border-slate-100 pb-4 mb-4 gap-3">
                    <div>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-900 flex items-center gap-2">
                            <span class="w-6 h-6 rounded-md bg-red-50 text-red-700 flex items-center justify-center">
                                <i data-lucide="git-branch" class="w-3.5 h-3.5"></i>
                            </span>
                            <span>Arborescence Résolue (menu_tree) : {{ $activeMenu->name }}</span>
                        </h2>
                        <p class="text-[11px] text-slate-500 mt-1">
                            Glissez-déposez un élément sur un autre pour changer son parent, ou sur la zone supérieure pour le replacer à la racine.
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-1 text-xs font-mono font-medium bg-slate-50 text-slate-700 rounded-lg border border-slate-200">
                            <span x-text="treeNodes.length"></span> <span x-text="treeNodes.length > 1 ? 'nœuds' : 'nœud'"></span>
                        </span>
                        <button
                            type="button"
                            @click="createItemModalOpen = true"
                            class="btn btn-sm btn-primary inline-flex items-center gap-1.5 cursor-pointer"
                        >
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                            <span>Ajouter</span>
                        </button>
                    </div>
                </div>

                <!-- ZONE DE DÉPÔT RACINE : Supprimer le parent (Niveau 0 Racine) -->
                <div
                    x-show="isDragging && draggedNode && draggedNode.parent_id"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    @dragover.prevent="dragOverRoot = true"
                    @dragleave="dragOverRoot = false"
                    @drop.prevent="dropOnRoot($event)"
                    class="mb-3.5 p-3.5 rounded-xl border-2 border-dashed transition text-center cursor-pointer flex items-center justify-center gap-2.5"
                    :class="dragOverRoot ? 'border-emerald-500 bg-emerald-50 text-emerald-800 ring-2 ring-emerald-200 scale-[1.01]' : 'border-slate-300 bg-slate-50/80 text-slate-600 hover:border-slate-400 hover:bg-slate-100/70'"
                >
                    <div class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                        <i data-lucide="corner-left-up" class="w-4 h-4"></i>
                    </div>
                    <span class="text-xs font-medium font-sans">
                        Déposer ici pour supprimer le parent de « <strong class="font-bold text-slate-900" x-text="draggedNode ? draggedNode.label : ''"></strong> » (Replacer au Niveau 0 Racine)
                    </span>
                </div>

                <!-- LISTE DES ÉLÉMENTS DE MENU ARBORESCENTE -->
                <template x-if="treeNodes.length > 0">
                    <div class="space-y-2 text-xs">
                        <template x-for="node in treeNodes" :key="node.id">
                            <div
                                draggable="true"
                                @dragstart="onDragStart(node, $event)"
                                @dragend="onDragEnd($event)"
                                @dragover.prevent="onDragOver(node, $event)"
                                @dragleave="onDragLeave(node, $event)"
                                @drop.prevent="onDrop(node, $event)"
                                class="group flex items-center justify-between py-2.5 px-3 rounded-xl border transition cursor-grab active:cursor-grabbing select-none"
                                :style="'margin-left: ' + ((node.depth - 1) * 22) + 'px'"
                                :class="{
                                    'bg-white border-slate-200/90 shadow-2xs hover:border-slate-300 hover:bg-slate-50/70 hover:shadow-xs': draggedNode?.id !== node.id && dragTargetNodeId !== node.id,
                                    'opacity-40 border-dashed border-red-400 bg-red-50/40': draggedNode?.id === node.id,
                                    'border-red-600 bg-red-50/70 ring-2 ring-red-200 scale-[1.01] shadow-sm': dragTargetNodeId === node.id && draggedNode?.id !== node.id
                                }"
                            >
                                <div class="flex items-center gap-2.5 truncate">
                                    <!-- Poignée de glisser-déposer -->
                                    <span class="text-slate-400 group-hover:text-slate-600 cursor-grab transition" title="Glisser pour changer le parent">
                                        <i data-lucide="grip-vertical" class="w-4 h-4"></i>
                                    </span>

                                    <!-- Badge de profondeur -->
                                    <span class="text-slate-600 font-semibold text-[10px] font-sans px-2 py-0.5 rounded-md bg-slate-100 border border-slate-200/80 shrink-0">
                                        Niv.<span x-text="node.depth"></span>
                                    </span>

                                    <!-- Libellé du menu -->
                                    <span class="font-bold text-slate-900 font-sans text-xs tracking-tight truncate" x-text="node.label"></span>

                                    <!-- Badge promotionnel optionnel -->
                                    <template x-if="node.badge_text">
                                        <span class="px-1.5 py-0.5 text-[9px] font-semibold bg-red-100 text-red-700 rounded-md font-sans border border-red-200/60 shrink-0" x-text="node.badge_text"></span>
                                    </template>

                                    <!-- URL résolue -->
                                    <span class="text-slate-500 font-mono text-[11px] bg-slate-50 border border-slate-200/70 px-2 py-0.5 rounded-md truncate max-w-xs" x-text="'→ ' + node.resolved_url"></span>

                                    <!-- Indicateur de cible de drop -->
                                    <template x-if="dragTargetNodeId === node.id && draggedNode?.id !== node.id">
                                        <span class="px-2 py-0.5 text-[10px] font-sans font-bold bg-red-600 text-white rounded-md shadow-xs animate-pulse shrink-0">
                                            Rattacher comme sous-élément
                                        </span>
                                    </template>
                                </div>

                                <div class="flex items-center gap-1 shrink-0 font-sans">
                                    <!-- Bouton rapide pour détacher le parent (si sous-élément) -->
                                    <template x-if="node.parent_id">
                                        <button
                                            type="button"
                                            @click="removeParent(node)"
                                            class="p-1.5 text-amber-600 hover:text-amber-700 hover:bg-amber-50 rounded-lg transition cursor-pointer"
                                            title="Supprimer le parent (Replacer à la racine)"
                                        >
                                            <i data-lucide="corner-left-up" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </template>

                                    <!-- Bouton Modifier l'élément -->
                                    <button
                                        type="button"
                                        @click="activeEditItemId = node.id"
                                        class="p-1.5 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-lg transition cursor-pointer"
                                        title="Modifier cet élément"
                                    >
                                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                    </button>

                                    <!-- Bouton Supprimer l'élément (via dialogue de confirmation réutilisable) -->
                                    <form :id="'delete-item-form-' + node.id" :action="'/admin/menu-items/' + node.id" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="button"
                                            @click="confirmDeleteItem(node, $el.closest('form'))"
                                            class="p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition cursor-pointer"
                                            title="Retirer du menu"
                                        >
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                <template x-if="treeNodes.length === 0">
                    <div class="text-center py-12 px-4 space-y-3 bg-slate-50/50 rounded-xl border border-dashed border-slate-200">
                        <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto">
                            <i data-lucide="compass" class="w-6 h-6"></i>
                        </div>
                        <div class="space-y-1">
                            <p class="text-sm font-semibold text-slate-900">Ce menu ne contient aucun élément</p>
                            <p class="text-xs text-slate-500">Ajoutez des rubriques, des articles ou des liens personnalisés pour composer votre navigation.</p>
                        </div>
                        <button
                            type="button"
                            @click="createItemModalOpen = true"
                            class="mt-2 btn btn-sm btn-primary inline-flex items-center gap-1.5 cursor-pointer"
                        >
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            <span>Ajouter le premier élément</span>
                        </button>
                    </div>
                </template>
            </div>
        </div>

        <!-- ==================================================== -->
        <!-- DIALOG 1 : AJOUTER UN ÉLÉMENT AU MENU                -->
        <!-- ==================================================== -->
        <div
            x-show="createItemModalOpen"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/40 backdrop-blur-xs"
            @keydown.escape.window="createItemModalOpen = false"
        >
            <div
                @click.outside="createItemModalOpen = false"
                class="bg-white rounded-2xl border border-slate-200 shadow-2xl w-full max-w-xl overflow-hidden flex flex-col max-h-[90vh]"
            >
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/70 shrink-0">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                            <i data-lucide="plus-circle" class="w-4 h-4 text-red-700"></i>
                            <span>Ajouter un élément au menu</span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Emplacement : <strong class="text-slate-800">{{ $activeMenu->name }}</strong> ({{ $activeMenu->location }})</p>
                    </div>
                    <button
                        type="button"
                        @click="createItemModalOpen = false"
                        class="p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-200/60 rounded-lg transition cursor-pointer"
                    >
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form action="{{ route('admin.menus.items.store', $activeMenu) }}" method="POST" class="p-6 space-y-4 overflow-y-auto flex-1">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="admin-label">Libellé (Label) *</label>
                            <input type="text" name="label" required placeholder="Ex: Grands Dossiers" class="admin-input">
                        </div>
                        <div>
                            <label class="admin-label">Élément Parent</label>
                            <x-ui.select
                                name="parent_id"
                                :options="$createParentOptions"
                                value=""
                                placeholder="— Niveau 0 (Racine) —"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="admin-label">Type de cible *</label>
                        <x-ui.select
                            name="type"
                            :options="$menuTypeOptions"
                            value="category"
                            :searchable="false"
                        />
                    </div>

                    <!-- Cible : Catégorie -->
                    <div x-show="createItemType === 'category'">
                        <label class="admin-label">Choisir la Catégorie *</label>
                        <x-ui.select
                            name="category_id"
                            :options="$categoryMenuOptions"
                            value=""
                            placeholder="Rechercher une catégorie..."
                        />
                    </div>

                    <!-- Cible : Article / Page -->
                    <div x-show="createItemType === 'post'" style="display: none;">
                        <label class="admin-label">Choisir l'Article ou la Page *</label>
                        <x-ui.select
                            name="post_id"
                            :options="$postMenuOptions"
                            value=""
                            placeholder="Rechercher un article ou une page..."
                        />
                    </div>

                    <!-- Cible : Custom URL -->
                    <div x-show="createItemType === 'custom_url'" style="display: none;">
                        <label class="admin-label">URL personnalisée *</label>
                        <input type="text" name="custom_url" placeholder="Ex: /search ou https://..." class="admin-input font-mono text-xs">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="admin-label">Badge</label>
                            <input type="text" name="badge_text" placeholder="Ex: Nouveau" class="admin-input">
                        </div>
                        <div>
                            <label class="admin-label">Ordre</label>
                            <input type="number" name="display_order" value="10" class="admin-input">
                        </div>
                        <div>
                            <label class="admin-label">Cible</label>
                            <x-ui.select
                                name="target"
                                :options="$targetOptions"
                                value="_self"
                                :searchable="false"
                            />
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3 shrink-0">
                        <button
                            type="button"
                            @click="createItemModalOpen = false"
                            class="btn btn-md btn-secondary cursor-pointer"
                        >
                            <span>Annuler</span>
                        </button>
                        <button
                            type="submit"
                            class="btn btn-md btn-primary justify-center cursor-pointer"
                        >
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            <span>Ajouter au Menu</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ==================================================== -->
        <!-- DIALOG 2 : MODIFIER UN ÉLÉMENT DU MENU               -->
        <!-- ==================================================== -->
        @foreach($activeMenu->items as $item)
            @php
                $itemParentOptions = $buildParentOptions($item);
                $itemTypeValue = $item->type instanceof \BackedEnum ? $item->type->value : (string) $item->type;
            @endphp
            <div
                x-show="activeEditItemId === '{{ $item->id }}'"
                x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/40 backdrop-blur-xs"
                @keydown.escape.window="activeEditItemId = null"
            >
                <div
                    @click.outside="activeEditItemId = null"
                    class="bg-white rounded-2xl border border-slate-200 shadow-2xl w-full max-w-xl overflow-hidden flex flex-col max-h-[90vh]"
                    x-data="{ editItemType: '{{ $itemTypeValue }}' }"
                    @select-change.window="if ($event.detail.name === 'type') editItemType = $event.detail.value"
                >
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/70 shrink-0">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                <i data-lucide="edit-3" class="w-4 h-4 text-sky-600"></i>
                                <span>Modifier l'élément de menu</span>
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">Édition de : <strong class="text-slate-800">{{ $item->label }}</strong></p>
                        </div>
                        <button
                            type="button"
                            @click="activeEditItemId = null"
                            class="p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-200/60 rounded-lg transition cursor-pointer"
                        >
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <form action="{{ route('admin.menus.items.update', $item) }}" method="POST" class="p-6 space-y-4 overflow-y-auto flex-1">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="admin-label">Libellé (Label) *</label>
                                <input type="text" name="label" value="{{ $item->label }}" required class="admin-input">
                            </div>
                            <div>
                                <label class="admin-label">Élément Parent</label>
                                <x-ui.select
                                    name="parent_id"
                                    :options="$itemParentOptions"
                                    :value="$item->parent_id ?? ''"
                                    placeholder="— Niveau 0 (Racine) —"
                                />
                            </div>
                        </div>

                        <div>
                            <label class="admin-label">Type de cible *</label>
                            <x-ui.select
                                name="type"
                                :options="$menuTypeOptions"
                                :value="$itemTypeValue"
                                :searchable="false"
                            />
                        </div>

                        <!-- Cible : Catégorie -->
                        <div x-show="editItemType === 'category'">
                            <label class="admin-label">Choisir la Catégorie *</label>
                            <x-ui.select
                                name="category_id"
                                :options="$categoryMenuOptions"
                                :value="$item->category_id ?? ''"
                                placeholder="Rechercher une catégorie..."
                            />
                        </div>

                        <!-- Cible : Article / Page -->
                        <div x-show="editItemType === 'post'" style="display: none;">
                            <label class="admin-label">Choisir l'Article ou la Page *</label>
                            <x-ui.select
                                name="post_id"
                                :options="$postMenuOptions"
                                :value="$item->post_id ?? ''"
                                placeholder="Rechercher un article ou une page..."
                            />
                        </div>

                        <!-- Cible : Custom URL -->
                        <div x-show="editItemType === 'custom_url'" style="display: none;">
                            <label class="admin-label">URL personnalisée *</label>
                            <input type="text" name="custom_url" value="{{ $item->custom_url }}" placeholder="Ex: /search ou https://..." class="admin-input font-mono text-xs">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="admin-label">Badge</label>
                                <input type="text" name="badge_text" value="{{ $item->badge_text }}" placeholder="Ex: Nouveau" class="admin-input">
                            </div>
                            <div>
                                <label class="admin-label">Ordre</label>
                                <input type="number" name="display_order" value="{{ $item->display_order }}" class="admin-input">
                            </div>
                            <div>
                                <label class="admin-label">Cible</label>
                                <x-ui.select
                                    name="target"
                                    :options="$targetOptions"
                                    :value="$item->target"
                                    :searchable="false"
                                />
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-100 flex items-center justify-between gap-3 shrink-0">
                            <button
                                type="button"
                                onclick="window.confirmAction({
                                    title: 'Retirer cet élément du menu',
                                    message: 'Êtes-vous sûr de vouloir retirer « {{ addslashes($item->label) }} » du menu ? Tous ses éventuels sous-éléments seront également supprimés.',
                                    formId: 'delete-item-modal-form-{{ $item->id }}',
                                    confirmText: 'Retirer du menu',
                                    cancelText: 'Annuler',
                                    variant: 'danger',
                                    icon: 'trash-2'
                                })"
                                class="btn btn-md text-rose-600 hover:text-rose-700 hover:bg-rose-50 border border-slate-200 hover:border-rose-200 cursor-pointer inline-flex items-center gap-1.5"
                            >
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                <span>Supprimer</span>
                            </button>

                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    @click="activeEditItemId = null"
                                    class="btn btn-md btn-secondary cursor-pointer"
                                >
                                    <span>Annuler</span>
                                </button>
                                <button
                                    type="submit"
                                    class="btn btn-md btn-primary justify-center cursor-pointer"
                                >
                                    <i data-lucide="check" class="w-4 h-4"></i>
                                    <span>Enregistrer les modifications</span>
                                </button>
                            </div>
                        </div>
                    </form>

                    <form id="delete-item-modal-form-{{ $item->id }}" action="{{ route('admin.menus.items.destroy', $item) }}" method="POST" class="hidden">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>
            </div>
        @endforeach

        <!-- ==================================================== -->
        <!-- DIALOG 3 : MODIFIER LE MENU (Nom & Description)     -->
        <!-- ==================================================== -->
        <div
            x-show="editMenuModalOpen"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/40 backdrop-blur-xs"
            @keydown.escape.window="editMenuModalOpen = false"
        >
            <div
                @click.outside="editMenuModalOpen = false"
                class="bg-white rounded-2xl border border-slate-200 shadow-2xl w-full max-w-lg overflow-hidden flex flex-col"
            >
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/70 shrink-0">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                            <i data-lucide="settings-2" class="w-4 h-4 text-red-700"></i>
                            <span>Paramètres du Menu</span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Emplacement système : <code class="font-mono text-slate-800">{{ $activeMenu->location }}</code></p>
                    </div>
                    <button
                        type="button"
                        @click="editMenuModalOpen = false"
                        class="p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-200/60 rounded-lg transition cursor-pointer"
                    >
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form action="{{ route('admin.menus.update', $activeMenu) }}" method="POST" class="p-6 space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="admin-label">Nom du Menu *</label>
                        <input type="text" name="name" value="{{ $activeMenu->name }}" required class="admin-input">
                    </div>

                    <div>
                        <label class="admin-label">Description ou Rôle</label>
                        <textarea name="description" rows="3" class="admin-input text-xs" placeholder="Ex: Navigation principale affichée dans l'en-tête du magazine...">{{ $activeMenu->description }}</textarea>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button
                            type="button"
                            @click="editMenuModalOpen = false"
                            class="btn btn-md btn-secondary cursor-pointer"
                        >
                            <span>Annuler</span>
                        </button>
                        <button
                            type="submit"
                            class="btn btn-md btn-primary justify-center cursor-pointer"
                        >
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span>Enregistrer le Menu</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>

<!-- SCRIPT ALPINE.JS : Gestion du glisser-déposer, modification d'élément et notifications toast -->
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('adminMenuManager', (config) => ({
        activeMenu: config.activeMenu,
        treeNodes: config.initialTree || [],
        createItemModalOpen: config.hasErrors,
        createItemType: 'category',
        editMenuModalOpen: false,
        activeEditItemId: null,

        // Données Drag and Drop
        isDragging: false,
        draggedNode: null,
        dragTargetNodeId: null,
        dragOverRoot: false,

        // Notifications Toast
        toasts: [],
        toastCounter: 0,

        init() {
            if (config.sessionSuccess) {
                this.addToast('success', config.sessionSuccess, 'Succès');
            }
            if (config.sessionError) {
                this.addToast('error', config.sessionError, 'Erreur');
            }

            this.$watch('createItemModalOpen', val => {
                if (val) this.refreshIcons();
            });
            this.$watch('editMenuModalOpen', val => {
                if (val) this.refreshIcons();
            });
            this.$watch('activeEditItemId', val => {
                if (val) this.refreshIcons();
            });
            this.$watch('treeNodes', () => {
                this.refreshIcons();
            });
        },

        refreshIcons() {
            this.$nextTick(() => {
                if (window.lucide) window.lucide.createIcons();
            });
        },

        handleSelectChange(detail) {
            if (detail && detail.name === 'type') {
                this.createItemType = detail.value;
            }
        },

        addToast(type, message, title = '') {
            const id = ++this.toastCounter;
            const newToast = {
                id: id,
                type: type, // 'success' ou 'error'
                title: title || (type === 'success' ? 'Modification enregistrée' : 'Échec de la modification'),
                message: message,
                visible: true,
            };

            this.toasts.push(newToast);
            this.refreshIcons();

            setTimeout(() => {
                this.removeToast(id);
            }, 4500);
        },

        removeToast(id) {
            const idx = this.toasts.findIndex(t => t.id === id);
            if (idx !== -1) {
                this.toasts[idx].visible = false;
                setTimeout(() => {
                    this.toasts = this.toasts.filter(t => t.id !== id);
                }, 300);
            }
        },

        // --- GESTION DU GLISSER-DÉPOSER ---
        onDragStart(node, event) {
            this.draggedNode = node;
            this.isDragging = true;
            this.dragTargetNodeId = null;
            this.dragOverRoot = false;

            if (event.dataTransfer) {
                event.dataTransfer.effectAllowed = 'move';
                event.dataTransfer.setData('text/plain', node.id);
            }
        },

        onDragEnd(event) {
            this.isDragging = false;
            this.draggedNode = null;
            this.dragTargetNodeId = null;
            this.dragOverRoot = false;
        },

        onDragOver(node, event) {
            if (!this.draggedNode || this.draggedNode.id === node.id) {
                return;
            }
            this.dragTargetNodeId = node.id;
        },

        onDragLeave(node, event) {
            if (this.dragTargetNodeId === node.id) {
                this.dragTargetNodeId = null;
            }
        },

        async dropOnNode(targetNode, event) {
            const dragged = this.draggedNode;
            this.onDragEnd(event);

            if (!dragged || dragged.id === targetNode.id) {
                return;
            }

            await this.executeReparent(dragged.id, targetNode.id);
        },

        async dropOnRoot(event) {
            const dragged = this.draggedNode;
            this.onDragEnd(event);

            if (!dragged || !dragged.parent_id) {
                return;
            }

            await this.executeReparent(dragged.id, null);
        },

        async removeParent(node) {
            await this.executeReparent(node.id, null);
        },

        async executeReparent(itemId, newParentId) {
            const url = config.reparentUrlTemplate.replace(':id', itemId);

            try {
                const response = await fetch(url, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': config.csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ parent_id: newParentId }),
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    this.addToast('error', data.message || 'Échec du déplacement de l\'élément.', 'Erreur');
                    return;
                }

                // Succès : actualiser l'arborescence récursive en temps réel
                if (data.tree) {
                    this.treeNodes = data.tree;
                }

                this.addToast('success', data.message, 'Succès');
            } catch (err) {
                this.addToast('error', 'Erreur réseau lors du glisser-déposer.', 'Erreur');
            }
        },

        // --- GESTION DE LA SUPPRESSION AVEC LE CONFIRM DIALOG RÉUTILISABLE ---
        confirmDeleteItem(node, formEl) {
            const label = node?.label || 'cet élément';
            const formId = 'delete-item-form-' + node.id;
            const targetForm = formEl || document.getElementById(formId);

            if (typeof window.confirmAction === 'function') {
                window.confirmAction({
                    title: 'Retirer cet élément du menu',
                    message: `Êtes-vous sûr de vouloir retirer « ${label} » du menu ? Tous ses éventuels sous-éléments seront également supprimés.`,
                    formId: formId,
                    form: targetForm,
                    onConfirm: () => {
                        if (targetForm && typeof targetForm.submit === 'function') {
                            targetForm.submit();
                        }
                    },
                    confirmText: 'Retirer du menu',
                    cancelText: 'Annuler',
                    variant: 'danger',
                    icon: 'trash-2'
                });
            } else if (confirm(`Retirer « ${label} » du menu ?`)) {
                if (targetForm && typeof targetForm.submit === 'function') {
                    targetForm.submit();
                }
            }
        },
    }));
});
</script>
@endsection