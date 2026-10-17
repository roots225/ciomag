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

    <form action="{{ $product->exists ? route('admin.shop.update', $product) : route('admin.shop.store') }}" method="POST"
        class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        @csrf
        @if($product->exists)
            @method('PUT')
        @endif

        <div class="lg:col-span-8 space-y-6">
            <div class="admin-card p-5 sm:p-6 space-y-4">
                <div>
                    <label class="admin-label">Titre du magazine ou de l'ouvrage *</label>
                    <input type="text" name="title" value="{{ old('title', $product->title) }}" required
                        class="admin-input text-base font-semibold">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="admin-label">Sous-titre / Édition</label>
                        <input type="text" name="subtitle" value="{{ old('subtitle', $product->subtitle) }}"
                            placeholder="Ex: Édition Septembre / Octobre 2026" class="admin-input">
                    </div>
                    <div>
                        <label class="admin-label">Numéro de parution (issue_number)</label>
                        <input type="text" name="issue_number" value="{{ old('issue_number', $product->issue_number) }}"
                            placeholder="Ex: N° 112" class="admin-input">
                    </div>
                </div>

                <div>
                    <label class="admin-label">Résumé court (Excerpt)</label>
                    <textarea name="excerpt" rows="3" class="admin-input">{{ old('excerpt', $product->excerpt) }}</textarea>
                </div>

                <div>
                    <label class="admin-label">Sommaire & Description complète (HTML)</label>
                    <textarea name="description" rows="8"
                        class="admin-input font-mono text-xs">{{ old('description', $product->description) }}</textarea>
                </div>
            </div>
        </div>

        <div class="lg:col-span-4 space-y-6">
            <div class="admin-card p-5 sm:p-6 space-y-4">
                <h2 class="text-sm font-semibold text-slate-900 border-b border-slate-100 pb-2.5">
                    Tarification & Stock
                </h2>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="admin-label">Type</label>
                        <x-ui.select name="type" :options="$typeOptions" :value="old('type', $product->type)"
                            :searchable="false" />
                    </div>
                    <div>
                        <label class="admin-label">Statut</label>
                        <x-ui.select name="status" :options="$statusOptions" :value="old('status', $product->status)"
                            :searchable="false" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="admin-label">Prix TTC (€) *</label>
                        <input type="number" step="0.01" name="price" value="{{ old('price', $product->price) }}" required
                            class="admin-input font-semibold">
                    </div>
                    <div>
                        <label class="admin-label">Prix barré (€)</label>
                        <input type="number" step="0.01" name="compare_at_price"
                            value="{{ old('compare_at_price', $product->compare_at_price) }}" class="admin-input">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="admin-label">Stock disponible</label>
                        <input type="number" name="stock" value="{{ old('stock', $product->stock) }}" class="admin-input">
                    </div>
                    <div>
                        <label class="admin-label">Nombre de pages</label>
                        <input type="number" name="pages_count"
                            value="{{ old('pages_count', $product->pages_count ?? 96) }}" class="admin-input">
                    </div>
                </div>

                <div class="space-y-2 pt-2 border-t border-slate-100">
                    <x-ui.checkbox name="is_digital" value="1" :checked="old('is_digital', $product->is_digital)"
                        label="Inclut une version numérique PDF HD" />
                    <x-ui.checkbox name="is_featured" value="1" :checked="old('is_featured', $product->is_featured)"
                        label="Mettre en avant dans la boutique" />
                </div>

                <button type="submit" class="w-auto btn btn-md btn-primary">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Enregistrer le produit</span>
                </button>
            </div>

            <div class="admin-card p-5 sm:p-6 space-y-3">
                <x-media-picker name="cover_url" valueMode="url" :value="old('cover_url', $product->cover_url)"
                    :previewUrl="old('cover_url', $product->cover_url)" label="Couverture du Magazine / Livre" />
            </div>
        </div>
    </form>
@endsection