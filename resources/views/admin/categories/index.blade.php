@extends('layouts.admin')

@section('title', 'Catégories & Tags')
@section('subtitle', 'Arborescence des rubriques du magazine (avec média de couverture) et mots-clés')

@section('content')
@php
    $parentCategoryOptions = ['' => '— Racine (Niveau 0) —'];
    foreach (($allCategories ?? $categories) as $c) {
        $parentCategoryOptions[$c->id] = ($c->parent ? $c->parent->name . ' › ' : '') . $c->name;
    }
@endphp

<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

    <!-- Catégories Hiérarchiques (7 colonnes) -->
    <div class="lg:col-span-7 space-y-6">
        <div class="admin-card p-5 sm:p-6 space-y-4">
            <h2 class="text-sm font-semibold text-slate-900 flex items-center gap-2 border-b border-slate-100 pb-3">
                <i data-lucide="folder-plus" class="w-4 h-4 text-red-700"></i>
                <span>Ajouter une Rubrique (Catégorie)</span>
            </h2>
            <form action="{{ route('admin.categories.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="admin-label">Nom *</label>
                        <input type="text" name="name" required placeholder="Ex: Souveraineté Numérique" class="admin-input">
                    </div>
                    <div>
                        <label class="admin-label">Slug (auto si vide)</label>
                        <input type="text" name="slug" placeholder="souverainete-numerique" class="admin-input font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="admin-label">Catégorie Parente</label>
                        <x-ui.select
                            name="parent_id"
                            :options="$parentCategoryOptions"
                            value=""
                            placeholder="— Racine (Niveau 0) —"
                        />
                    </div>
                    <div>
                        <label class="admin-label">Ordre d'affichage</label>
                        <input type="number" name="display_order" value="0" class="admin-input">
                    </div>
                </div>

                <div>
                    <label class="admin-label">Description éditoriale</label>
                    <textarea name="description" rows="2" class="admin-input"></textarea>
                </div>

                <div>
                    <x-media-picker
                        name="og_image"
                        valueMode="url"
                        value=""
                        label="Visuel / Image de couverture de la rubrique"
                    />
                </div>

                <button type="submit" class="btn btn-sm btn-primary">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Créer la catégorie</span>
                </button>
            </form>
        </div>

        <div class="admin-card overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-slate-900 flex items-center gap-2">
                    <i data-lucide="folder-tree" class="w-4 h-4 text-red-700"></i>
                    <span>Rubriques Existantes ({{ $categories->total() }})</span>
                </h2>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach($categories as $cat)
                    <div class="p-4 flex items-center justify-between gap-4 hover:bg-slate-50/70 transition-colors">
                        <div class="flex items-center gap-3">
                            @if(!empty($cat->seo_meta['og_image']))
                                <img src="{{ $cat->seo_meta['og_image'] }}" alt="{{ $cat->name }}" class="w-10 h-10 rounded-lg object-cover border border-slate-200 shrink-0">
                            @else
                                <div class="w-10 h-10 rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center shrink-0">
                                    <i data-lucide="folder" class="w-4 h-4"></i>
                                </div>
                            @endif
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-xs text-slate-900">
                                        {{ $cat->parent ? $cat->parent->name . ' › ' : '' }}{{ $cat->name }}
                                    </span>
                                    <span class="admin-badge badge-neutral text-[10px] py-0 px-1.5">{{ $cat->posts_count }} art.</span>
                                </div>
                                <div class="text-[11px] text-slate-400 font-mono mt-0.5">/category/{{ $cat->slug }} • Ordre: {{ $cat->display_order }}</div>
                            </div>
                        </div>
                        <form action="{{ route('admin.categories.destroy', $cat) }}" method="POST" onsubmit="return confirm('Supprimer cette catégorie ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-1.5 text-rose-600 hover:bg-rose-50 rounded-lg transition cursor-pointer" title="Supprimer">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>

        <div>
            {{ $categories->links() }}
        </div>
    </div>

    <!-- Tags (5 colonnes) -->
    <div class="lg:col-span-5 space-y-6">
        <div class="admin-card p-5 sm:p-6 space-y-4">
            <h2 class="text-sm font-semibold text-slate-900 flex items-center gap-2 border-b border-slate-100 pb-3">
                <i data-lucide="tag" class="w-4 h-4 text-red-700"></i>
                <span>Ajouter un Tag</span>
            </h2>
            <form action="{{ route('admin.tags.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="admin-label">Nom du Tag *</label>
                    <input type="text" name="name" required placeholder="Ex: Souveraineté Cloud" class="admin-input">
                </div>
                <div>
                    <label class="admin-label">Description</label>
                    <input type="text" name="description" class="admin-input">
                </div>
                <button type="submit" class="btn btn-sm btn-dark">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Ajouter le Tag</span>
                </button>
            </form>
        </div>

        <div class="admin-card p-5 sm:p-6 space-y-3">
            <h2 class="text-sm font-semibold text-slate-900 flex items-center gap-2 border-b border-slate-100 pb-2.5">
                <i data-lucide="tags" class="w-4 h-4 text-red-700"></i>
                <span>Tags ({{ $tags->count() }})</span>
            </h2>
            <div class="space-y-2">
                @foreach($tags as $tag)
                    <div class="flex items-center justify-between py-1.5 border-b border-slate-100 last:border-none text-xs">
                        <div class="flex items-center gap-1.5">
                            <i data-lucide="hash" class="w-3.5 h-3.5 text-red-700"></i>
                            <span class="font-semibold text-slate-800">{{ $tag->name }}</span>
                            <span class="text-slate-400">({{ $tag->posts_count }})</span>
                        </div>
                        <form action="{{ route('admin.tags.destroy', $tag) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-1 text-rose-600 hover:bg-rose-50 rounded cursor-pointer" title="Supprimer">
                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

</div>
@endsection
