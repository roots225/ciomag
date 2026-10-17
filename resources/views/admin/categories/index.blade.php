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

<div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

    <!-- Catégories Hiérarchiques (7 colonnes) -->
    <div class="lg:col-span-7 space-y-6">
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-2xs">
            <h2 class="text-sm font-black uppercase tracking-wider text-slate-900 mb-4 flex items-center gap-2">
                <i data-lucide="folder-plus" class="w-4 h-4 text-red-700"></i>
                <span>Ajouter une Rubrique (Catégorie)</span>
            </h2>
            <form action="{{ route('admin.categories.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nom *</label>
                        <input type="text" name="name" required placeholder="Ex: Souveraineté Numérique" class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Slug (auto si vide)</label>
                        <input type="text" name="slug" placeholder="souverainete-numerique" class="w-full px-3.5 py-2.5 text-sm font-mono border border-slate-300 rounded-xl">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Catégorie Parente</label>
                        <x-ui.select
                            name="parent_id"
                            :options="$parentCategoryOptions"
                            value=""
                            placeholder="— Racine (Niveau 0) —"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Ordre d'affichage</label>
                        <input type="number" name="display_order" value="0" class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Description éditoriale</label>
                    <textarea name="description" rows="2" class="w-full px-3.5 py-2 text-sm border border-slate-300 rounded-xl"></textarea>
                </div>

                <div>
                    <x-media-picker
                        name="og_image"
                        valueMode="url"
                        value=""
                        label="Visuel / Image de couverture de la rubrique (seo_meta.og_image)"
                    />
                </div>

                <button type="submit" class="px-5 py-2.5 bg-red-700 hover:bg-red-800 text-white text-xs font-bold uppercase rounded-xl inline-flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Créer la catégorie</span>
                </button>
            </form>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-2xs">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <h2 class="text-sm font-black uppercase tracking-wider text-slate-900 flex items-center gap-2">
                    <i data-lucide="folder-tree" class="w-4 h-4 text-red-700"></i>
                    <span>Rubriques Existantes ({{ $categories->total() }})</span>
                </h2>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach($categories as $cat)
                    <div class="p-5 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3.5">
                            @if(!empty($cat->seo_meta['og_image']))
                                <img src="{{ $cat->seo_meta['og_image'] }}" alt="{{ $cat->name }}" class="w-11 h-11 rounded-lg object-cover border border-slate-200 shrink-0">
                            @else
                                <div class="w-11 h-11 rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center shrink-0">
                                    <i data-lucide="folder" class="w-5 h-5"></i>
                                </div>
                            @endif
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-sm text-slate-900">
                                        {{ $cat->parent ? $cat->parent->name . ' › ' : '' }}{{ $cat->name }}
                                    </span>
                                    <span class="px-2 py-0.5 text-[11px] bg-slate-100 text-slate-600 rounded-full">{{ $cat->posts_count }} art.</span>
                                </div>
                                <div class="text-xs text-slate-400 font-mono">/category/{{ $cat->slug }} • Ordre: {{ $cat->display_order }}</div>
                            </div>
                        </div>
                        <form action="{{ route('admin.categories.destroy', $cat) }}" method="POST" onsubmit="return confirm('Supprimer cette catégorie ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2 text-rose-600 hover:bg-rose-50 rounded-lg transition cursor-pointer" title="Supprimer">
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

    <!-- Tags Plats (5 colonnes) -->
    <div class="lg:col-span-5 space-y-6">
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-2xs">
            <h2 class="text-sm font-black uppercase tracking-wider text-slate-900 mb-4 flex items-center gap-2">
                <i data-lucide="tag" class="w-4 h-4 text-red-700"></i>
                <span>Ajouter un Tag</span>
            </h2>
            <form action="{{ route('admin.tags.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nom du Tag *</label>
                    <input type="text" name="name" required placeholder="Ex: Souveraineté Cloud" class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Description</label>
                    <input type="text" name="description" class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl">
                </div>
                <button type="submit" class="px-5 py-2.5 bg-slate-900 hover:bg-red-700 text-white text-xs font-bold uppercase rounded-xl inline-flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Ajouter le Tag</span>
                </button>
            </form>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-2xs">
            <h2 class="text-sm font-black uppercase tracking-wider text-slate-900 mb-4 flex items-center gap-2">
                <i data-lucide="tags" class="w-4 h-4 text-red-700"></i>
                <span>Tags ({{ $tags->count() }})</span>
            </h2>
            <div class="space-y-2.5">
                @foreach($tags as $tag)
                    <div class="flex items-center justify-between py-2 border-b border-slate-100 last:border-none text-sm">
                        <div class="flex items-center gap-1.5">
                            <i data-lucide="hash" class="w-3.5 h-3.5 text-red-700"></i>
                            <span class="font-bold text-slate-800">{{ $tag->name }}</span>
                            <span class="text-xs text-slate-400">({{ $tag->posts_count }} articles)</span>
                        </div>
                        <form action="{{ route('admin.tags.destroy', $tag) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-1.5 text-rose-600 hover:bg-rose-50 rounded-lg cursor-pointer" title="Supprimer">
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
