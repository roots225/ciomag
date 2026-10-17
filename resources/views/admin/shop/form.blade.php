@extends('layouts.admin')

@section('title', $product->exists ? 'Éditer : ' . $product->title : 'Nouveau Produit Boutique')
@section('subtitle', 'Configuration d\'un magazine, livre ou baromètre pour la boutique CIOMAG')

@section('content')
@php
    $typeOptions = [
        'magazine' => 'Magazine Bimestriel CIOMAG',
        'book' => 'Livre / Ouvrage de référence',
        'report' => 'Étude & Livre Blanc',
        'bundle' => 'Pack / Collection',
    ];
    $statusOptions = [
        'published' => 'Publié (En vente)',
        'draft' => 'Brouillon',
        'archived' => 'Archivé',
    ];
@endphp

<form action="{{ $product->exists ? route('admin.shop.update', $product) : route('admin.shop.store') }}" method="POST" class="grid grid-cols-1 lg:grid-cols-12 gap-8">
    @csrf
    @if($product->exists)
        @method('PUT')
    @endif

    <div class="lg:col-span-8 space-y-6">
        <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-5 shadow-2xs">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Titre du magazine ou de l'ouvrage *</label>
                <input type="text" name="title" value="{{ old('title', $product->title) }}" required class="w-full px-4 py-2.5 text-base font-bold border border-slate-300 rounded-xl">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Sous-titre / Édition</label>
                    <input type="text" name="subtitle" value="{{ old('subtitle', $product->subtitle) }}" placeholder="Ex: Édition Septembre / Octobre 2026" class="w-full px-3.5 py-2 text-sm border border-slate-300 rounded-xl">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Numéro de parution (`issue_number`)</label>
                    <input type="text" name="issue_number" value="{{ old('issue_number', $product->issue_number) }}" placeholder="Ex: N° 112" class="w-full px-3.5 py-2 text-sm border border-slate-300 rounded-xl">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Résumé court (Excerpt)</label>
                <textarea name="excerpt" rows="3" class="w-full px-3.5 py-2 text-sm border border-slate-300 rounded-xl">{{ old('excerpt', $product->excerpt) }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Sommaire & Description complète (HTML)</label>
                <textarea name="description" rows="8" class="w-full px-3.5 py-2 text-sm font-mono border border-slate-300 rounded-xl">{{ old('description', $product->description) }}</textarea>
            </div>
        </div>
    </div>

    <div class="lg:col-span-4 space-y-6">
        <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4 shadow-2xs">
            <h2 class="text-sm font-black uppercase tracking-wider text-slate-900 border-b border-slate-100 pb-2.5">
                Tarification & Stock
            </h2>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Type</label>
                    <x-ui.select name="type" :options="$typeOptions" :value="old('type', $product->type)" :searchable="false" />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Statut</label>
                    <x-ui.select name="status" :options="$statusOptions" :value="old('status', $product->status)" :searchable="false" />
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Prix TTC (€) *</label>
                    <input type="number" step="0.01" name="price" value="{{ old('price', $product->price) }}" required class="w-full px-3.5 py-2 text-sm font-bold border border-slate-300 rounded-xl">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Prix barré (€)</label>
                    <input type="number" step="0.01" name="compare_at_price" value="{{ old('compare_at_price', $product->compare_at_price) }}" class="w-full px-3.5 py-2 text-sm border border-slate-300 rounded-xl">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Stock disponible</label>
                    <input type="number" name="stock" value="{{ old('stock', $product->stock) }}" class="w-full px-3.5 py-2 text-sm border border-slate-300 rounded-xl">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nombre de pages</label>
                    <input type="number" name="pages_count" value="{{ old('pages_count', $product->pages_count ?? 96) }}" class="w-full px-3.5 py-2 text-sm border border-slate-300 rounded-xl">
                </div>
            </div>

            <div class="space-y-2.5 pt-2 border-t border-slate-100">
                <x-ui.checkbox
                    name="is_digital"
                    value="1"
                    :checked="old('is_digital', $product->is_digital)"
                    label="Inclut une version numérique PDF HD"
                />
                <x-ui.checkbox
                    name="is_featured"
                    value="1"
                    :checked="old('is_featured', $product->is_featured)"
                    label="Mettre en avant dans la boutique"
                />
            </div>

            <button type="submit" class="w-full py-3 bg-red-700 hover:bg-red-800 text-white text-xs font-black uppercase tracking-wider rounded-xl inline-flex items-center justify-center gap-2 cursor-pointer">
                <i data-lucide="save" class="w-4 h-4"></i>
                <span>Enregistrer le produit</span>
            </button>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-3 shadow-2xs">
            <x-media-picker
                name="cover_url"
                valueMode="url"
                :value="old('cover_url', $product->cover_url)"
                :previewUrl="old('cover_url', $product->cover_url)"
                label="Couverture du Magazine / Livre (Media Manager & Cropper)"
            />
        </div>
    </div>
</form>
@endsection
