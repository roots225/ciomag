@extends('layouts.admin')

@section('title', $post->exists ? 'Éditer : ' . $post->title : 'Nouvelle Publication')
@section('subtitle', 'Articles avec TinyMCE • Pages avec le constructeur visuel GrapesJS • Médias & SEO JSON')

@push('head')
    <!-- TinyMCE 7 (Sans clé API requise via jsDelivr) -->
    <script src="https://cdn.jsdelivr.net/npm/tinymce@7.6.0/tinymce.min.js" referrerpolicy="origin"></script>

    <!-- GrapesJS Visual Page Builder pour les Pages statiques -->
    <link rel="stylesheet" href="https://unpkg.com/grapesjs@0.21.13/dist/css/grapes.min.css">
    <script src="https://unpkg.com/grapesjs@0.21.13/dist/grapes.min.js"></script>
    <script src="https://unpkg.com/grapesjs-blocks-basic@1.0.2/dist/index.js"></script>
    <style>
        .gjs-one-bg { background-color: #0f172a !important; }
        .gjs-two-color { color: #f8fafc !important; }
        .gjs-three-bg { background-color: #b91c1c !important; color: #ffffff !important; }
        .gjs-four-color, .gjs-four-color-h:hover { color: #ef4444 !important; }
        .tox-tinymce { border-radius: 0.75rem !important; border-color: #cbd5e1 !important; }
    </style>
@endpush

@section('content')
@php
    $selectedCategoryIds = old('category_ids', $post->exists ? $post->categories->pluck('id')->all() : []);
    $primaryCategoryId = old('primary_category_id', $post->exists ? ($post->categories->firstWhere('pivot.is_primary', true)?->id ?? $post->categories->first()?->id) : null);
    $selectedTagIds = old('tag_ids', $post->exists ? $post->tags->pluck('id')->all() : []);
    $initialType = old('type', $post->type?->value ?? request('type', 'post'));

    $typeOptions = [];
    foreach (\App\Enums\PostType::cases() as $pt) {
        $typeOptions[$pt->value] = $pt->label();
    }

    $statusOptions = [];
    foreach (\App\Enums\PostStatus::cases() as $ps) {
        $statusOptions[$ps->value] = $ps->label();
    }

    $formatOptions = [];
    foreach (\App\Enums\ContentFormat::cases() as $fmt) {
        $formatOptions[$fmt->value] = $fmt->label();
    }

    $parentPageOptions = ['' => '— Aucune (Racine) —'];
    foreach ($parentPages as $pp) {
        $parentPageOptions[$pp->id] = $pp->title;
    }

    $categorySelectOptions = ['' => '— Sélectionner la rubrique principale —'];
    foreach ($categories as $cat) {
        $categorySelectOptions[$cat->id] = ($cat->parent ? $cat->parent->name . ' › ' : '') . $cat->name;
    }
@endphp

<div
    x-data="postEditorManager(@js($initialType))"
    x-init="initEditors()"
    @select-change.window="handleSelectChange($event.detail)"
>
    <form
        id="post-editor-form"
        action="{{ $post->exists ? route('admin.posts.update', $post) : route('admin.posts.store') }}"
        method="POST"
        @submit="beforeFormSubmit($event)"
        class="grid grid-cols-1 lg:grid-cols-12 gap-8"
    >
        @csrf
        @if($post->exists)
            @method('PUT')
        @endif

        <!-- Colonne Principale (8 colonnes) -->
        <div class="lg:col-span-8 space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-5 shadow-2xs">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Titre de la publication *</label>
                    <input
                        type="text"
                        name="title"
                        value="{{ old('title', $post->title) }}"
                        required
                        placeholder="Titre de l'article ou de la page..."
                        class="w-full px-4 py-2.5 text-base font-bold border border-slate-300 rounded-xl focus:outline-none focus:border-red-700"
                    >
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Slug URL (auto-généré si vide)</label>
                        <input
                            type="text"
                            name="slug"
                            value="{{ old('slug', $post->slug) }}"
                            placeholder="ex: ia-generative-dsi-2026"
                            class="w-full px-3.5 py-2.5 text-sm font-mono border border-slate-300 rounded-xl focus:outline-none focus:border-red-700"
                        >
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Format du contenu</label>
                        <x-ui.select
                            name="content_format"
                            :options="$formatOptions"
                            :value="old('content_format', $post->content_format?->value ?? 'html')"
                            :searchable="false"
                        />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Extrait éditorial</label>
                    <textarea
                        name="excerpt"
                        rows="3"
                        placeholder="Synthèse de 2 à 3 phrases affichée en tête d'article et dans les listes..."
                        class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:outline-none focus:border-red-700"
                    >{{ old('excerpt', $post->excerpt) }}</textarea>
                </div>

                <!-- Barre de commutation d'éditeur (TinyMCE vs GrapesJS Visual Builder) -->
                <div class="pt-2">
                    <!-- Champ sous-jacent synchronisé -->
                    <div x-show="editorMode === 'tinymce'">
                        <textarea
                            id="tinymce-content"
                            name="content"
                            rows="18"
                            class="w-full px-4 py-3 text-sm font-mono border border-slate-300 rounded-xl"
                        >{{ old('content', $post->content) }}</textarea>
                    </div>

                    <!-- Conteneur GrapesJS pour l'édition visuelle de Pages -->
                    <div x-show="editorMode === 'grapesjs'" style="display: none;" class="rounded-xl overflow-hidden border border-slate-300 shadow-sm">
                        <div id="gjs-page-builder" style="height: 620px; width: 100%;"></div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5 flex items-center gap-1.5">
                        <i data-lucide="git-commit-horizontal" class="w-3.5 h-3.5 text-red-700"></i>
                        <span>Note de révision (`post_revisions.change_summary`)</span>
                    </label>
                    <input
                        type="text"
                        name="change_summary"
                        placeholder="Ex: Ajout des chiffres du baromètre cybersécurité..."
                        class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:outline-none focus:border-red-700"
                    >
                </div>
            </div>

            <!-- Métadonnées SEO (JSON seo_meta) -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-5 shadow-2xs">
                <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                    <h2 class="text-sm font-black uppercase tracking-wider text-slate-900 flex items-center gap-2">
                        <i data-lucide="search-check" class="w-4 h-4 text-red-700"></i>
                        <span>SEO</span>
                    </h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Meta Title</label>
                        <input
                            type="text"
                            name="seo_meta_title"
                            value="{{ old('seo_meta_title', $post->seo_meta['meta_title'] ?? '') }}"
                            placeholder="Titre optimisé pour Google..."
                            class="w-full px-3.5 py-2 text-sm border border-slate-300 rounded-xl"
                        >
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Canonical URL</label>
                        <input
                            type="url"
                            name="seo_canonical_url"
                            value="{{ old('seo_canonical_url', $post->seo_meta['canonical_url'] ?? '') }}"
                            placeholder="https://..."
                            class="w-full px-3.5 py-2 text-sm border border-slate-300 rounded-xl"
                        >
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Meta Description</label>
                    <textarea
                        name="seo_meta_description"
                        rows="2"
                        placeholder="Description pour les moteurs de recherche (150-160 caractères)..."
                        class="w-full px-3.5 py-2 text-sm border border-slate-300 rounded-xl"
                    >{{ old('seo_meta_description', $post->seo_meta['meta_description'] ?? '') }}</textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-center pt-2">
                    <x-media-picker
                        name="seo_og_image"
                        valueMode="url"
                        :value="old('seo_og_image', $post->seo_meta['og_image'] ?? '')"
                        :previewUrl="old('seo_og_image', $post->seo_meta['og_image'] ?? '')"
                        label="Image OpenGraph Partage Social"
                    />

                    <div class="pt-4">
                        <x-ui.checkbox
                            name="seo_noindex"
                            value="1"
                            :checked="old('seo_noindex', !empty($post->seo_meta['noindex']))"
                            label="Exclure de l'indexation"
                            description="Empêche les moteurs de recherche d'indexer cette publication."
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Colonne Latérale (4 colonnes) -->
        <div class="lg:col-span-4 space-y-6">
            <!-- Publication & Statut -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4 shadow-2xs">
                <h2 class="text-sm font-black uppercase tracking-wider text-slate-900 border-b border-slate-100 pb-2.5 flex items-center gap-2">
                    <i data-lucide="send" class="w-4 h-4 text-red-700"></i>
                    <span>Publication & État</span>
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Type de contenu</label>
                        <x-ui.select
                            name="type"
                            :options="$typeOptions"
                            :value="$initialType"
                            :searchable="false"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Statut</label>
                        <x-ui.select
                            name="status"
                            :options="$statusOptions"
                            :value="old('status', $post->status?->value ?? 'draft')"
                            :searchable="false"
                        />
                    </div>
                </div>

                <div class="space-y-3 pt-2 border-t border-slate-100">
                    <x-ui.checkbox
                        name="is_featured"
                        value="1"
                        :checked="old('is_featured', $post->is_featured)"
                        label="Épingler « À la Une » (Sticky)"
                        description="Affiche cet article en priorité dans le Hero de la page d'accueil."
                    />

                    <x-ui.checkbox
                        name="is_premium"
                        value="1"
                        :checked="old('is_premium', $post->is_premium)"
                        label="Article Exclusif Abonnés (Paywall Premium)"
                        description="Réserve la lecture intégrale aux lecteurs titulaires d'un abonnement actif."
                    />

                    <x-ui.checkbox
                        name="allow_comments"
                        value="1"
                        :checked="old('allow_comments', $post->allow_comments)"
                        label="Autoriser les commentaires"
                        description="Permet aux lecteurs et membres de réagir sous l'article."
                    />
                </div>

                <div x-show="postType === 'page'">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Page parente (Hiérarchie)</label>
                    <x-ui.select
                        name="parent_id"
                        :options="$parentPageOptions"
                        :value="old('parent_id', $post->parent_id)"
                        placeholder="— Aucune (Racine) —"
                    />
                </div>

                <button type="submit" class="w-full py-2.5 bg-red-700 hover:bg-red-800 text-white text-sm font-bold rounded-xl transition inline-flex items-center justify-center gap-2 cursor-pointer shadow-sm">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>{{ $post->exists ? 'Enregistrer les modifications' : 'Publier / Enregistrer' }}</span>
                </button>
            </div>

            <!-- Diffusion Réseaux Sociaux (Facebook, LinkedIn, Twitter/X) -->
            <div x-show="postType === 'post'" class="bg-slate-900 text-white rounded-2xl p-6 space-y-4 shadow-2xs">
                <div class="flex items-center justify-between border-b border-slate-800 pb-2.5">
                    <h2 class="text-xs font-black uppercase tracking-wider text-amber-400 flex items-center gap-2">
                        <i data-lucide="share-2" class="w-4 h-4"></i>
                        <span>Diffuser sur les Réseaux Sociaux</span>
                    </h2>
                    <a href="{{ route('admin.social.index') }}" class="text-[11px] text-slate-400 hover:text-white underline">Paramètres API</a>
                </div>

                <p class="text-xs text-slate-300 leading-relaxed">
                    Cochez les plateformes sur lesquelles publier automatiquement cet article lors de l'enregistrement :
                </p>

                <div class="space-y-2.5 bg-white text-slate-900 p-3.5 rounded-xl">
                    <x-ui.checkbox
                        name="social_platforms[]"
                        value="linkedin"
                        :checked="false"
                        label="LinkedIn (Page Entreprise CIOMAG)"
                    />
                    <x-ui.checkbox
                        name="social_platforms[]"
                        value="twitter"
                        :checked="false"
                        label="X / Twitter (@CIOMAG_Officiel)"
                    />
                    <x-ui.checkbox
                        name="social_platforms[]"
                        value="facebook"
                        :checked="false"
                        label="Facebook (Page Officielle CIOMAG)"
                    />
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-300 mb-1">Message personnalisé (optionnel)</label>
                    <textarea
                        name="social_custom_message"
                        rows="2"
                        placeholder="Laissez vide pour utiliser le modèle automatique avec titre, extrait et hashtags..."
                        class="w-full px-3 py-2 text-xs bg-slate-800 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-amber-400"
                    ></textarea>
                </div>

                @if($post->exists && $post->relationLoaded('socialShares') && $post->socialShares->isNotEmpty())
                    <div class="pt-2 border-t border-slate-800 space-y-1.5">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Dernières diffusions :</span>
                        @foreach($post->socialShares->take(3) as $share)
                            <div class="flex items-center justify-between text-[11px] text-slate-300">
                                <span class="font-semibold">{{ $share->platformLabel() }}</span>
                                <span class="text-emerald-400">{{ $share->shared_at?->format('d/m H:i') }} ({{ $share->status }})</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Image à la Une (Composant Universel Media Picker avec Recadrage) -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-3 shadow-2xs">
                <h2 class="text-sm font-black uppercase tracking-wider text-slate-900 border-b border-slate-100 pb-2.5 flex items-center gap-2">
                    <i data-lucide="image" class="w-4 h-4 text-red-700"></i>
                    <span>Média de Couverture</span>
                </h2>

                <x-media-picker
                    name="featured_media_id"
                    valueMode="id"
                    :value="old('featured_media_id', $post->featured_media_id)"
                    :previewUrl="$post->featuredImageUrl()"
                    :previewName="$post->featuredMedia?->file_name"
                />
            </div>

            <!-- Catégories (avec is_primary) -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4 shadow-2xs">
                <h2 class="text-sm font-black uppercase tracking-wider text-slate-900 border-b border-slate-100 pb-2.5 flex items-center gap-2">
                    <i data-lucide="folder-tree" class="w-4 h-4 text-red-700"></i>
                    <span>Rubriques & Catégorie Principale</span>
                </h2>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Catégorie Principale</label>
                    <x-ui.select
                        name="primary_category_id"
                        :options="$categorySelectOptions"
                        :value="$primaryCategoryId"
                        placeholder="Choisir la rubrique principale..."
                    />
                </div>

                <div class="space-y-2 max-h-48 overflow-y-auto pr-1 pt-2 border-t border-slate-100">
                    <span class="block text-xs font-semibold text-slate-700 mb-2">Rubriques associées :</span>
                    @foreach($categories as $cat)
                        <div>
                            <x-ui.checkbox
                                name="category_ids[]"
                                :value="$cat->id"
                                :checked="in_array($cat->id, $selectedCategoryIds, true)"
                                :label="($cat->parent ? '↳ ' : '') . $cat->name"
                            />
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Tags -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-3 shadow-2xs">
                <h2 class="text-sm font-black uppercase tracking-wider text-slate-900 border-b border-slate-100 pb-2.5 flex items-center gap-2">
                    <i data-lucide="tags" class="w-4 h-4 text-red-700"></i>
                    <span>Mots-clés (Tags)</span>
                </h2>
                <div class="grid grid-cols-1 gap-2 max-h-48 overflow-y-auto pr-1">
                    @foreach($tags as $tag)
                        <x-ui.checkbox
                            name="tag_ids[]"
                            :value="$tag->id"
                            :checked="in_array($tag->id, $selectedTagIds, true)"
                            :label="'#' . $tag->name"
                        />
                    @endforeach
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Historique des Révisions (post_revisions) -->
@if($post->exists && $post->revisions->isNotEmpty())
    <div class="mt-8 bg-white rounded-2xl border border-slate-200 p-6 shadow-2xs">
        <h2 class="text-sm font-black uppercase tracking-wider text-slate-900 mb-4 flex items-center gap-2">
            <i data-lucide="history" class="w-4 h-4 text-red-700"></i>
            <span>Historique des Révisions ({{ $post->revisions->count() }})</span>
        </h2>
        <div class="divide-y divide-slate-100 text-xs">
            @foreach($post->revisions as $rev)
                <div class="py-3 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="font-bold text-slate-900">{{ $rev->created_at->format('d/m/Y à H:i') }}</span>
                        <span class="text-slate-500">par {{ $rev->editor?->display_name ?? 'Système' }}</span>
                        @if($rev->change_summary)
                            <span class="px-2 py-0.5 bg-slate-100 text-slate-700 rounded-xs font-medium">{{ $rev->change_summary }}</span>
                        @endif
                    </div>
                    <form action="{{ route('admin.posts.revisions.restore', [$post, $rev]) }}" method="POST">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 bg-slate-900 hover:bg-red-700 text-white font-semibold rounded-lg transition inline-flex items-center gap-1.5 cursor-pointer">
                            <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                            <span>Restaurer cette version</span>
                        </button>
                    </form>
                </div>
            @endforeach
        </div>
    </div>
@endif
@endsection

@push('scripts')
<script>
    function postEditorManager(initialType) {
        return {
            postType: initialType || 'post',
            editorMode: initialType === 'page' ? 'grapesjs' : 'tinymce',
            gjsEditor: null,
            tinyInitialized: false,

            initEditors() {
                this.initTinyMCE();
                if (this.editorMode === 'grapesjs') {
                    this.$nextTick(() => this.initGrapesJS());
                }
            },

            handleSelectChange(detail) {
                if (detail.name === 'type') {
                    this.postType = detail.value;
                    if (detail.value === 'page' && this.editorMode !== 'grapesjs') {
                        this.switchEditorMode('grapesjs');
                    } else if (detail.value === 'post' && this.editorMode !== 'tinymce') {
                        this.switchEditorMode('tinymce');
                    }
                }
            },

            switchEditorMode(mode) {
                if (this.editorMode === mode) return;

                // Synchroniser le contenu avant de basculer
                const textarea = document.getElementById('tinymce-content');
                if (this.editorMode === 'tinymce' && window.tinymce?.get('tinymce-content')) {
                    textarea.value = window.tinymce.get('tinymce-content').getContent();
                } else if (this.editorMode === 'grapesjs' && this.gjsEditor) {
                    textarea.value = this.getGrapesFullHtml();
                }

                this.editorMode = mode;

                if (mode === 'grapesjs') {
                    this.$nextTick(() => {
                        if (!this.gjsEditor) {
                            this.initGrapesJS();
                        } else {
                            this.gjsEditor.setComponents(textarea.value);
                        }
                    });
                } else if (mode === 'tinymce') {
                    this.$nextTick(() => {
                        if (window.tinymce?.get('tinymce-content')) {
                            window.tinymce.get('tinymce-content').setContent(textarea.value || '');
                        } else {
                            this.initTinyMCE();
                        }
                    });
                }
            },

            initTinyMCE() {
                if (!window.tinymce || window.tinymce.get('tinymce-content')) return;

                window.tinymce.init({
                    selector: '#tinymce-content',
                    height: 540,
                    menubar: 'file edit view insert format tools table',
                    promotion: false,
                    branding: false,
                    plugins: 'lists link image table code fullscreen wordcount visualblocks',
                    toolbar: 'undo redo | blocks | bold italic underline | alignleft aligncenter alignright | bullist numlist blockquote | link table ciomag_media | code fullscreen',
                    content_style: `
                        body { font-family: 'Instrument Sans', system-ui, sans-serif; font-size: 16px; line-height: 1.75; color: #1e293b; padding: 12px; }
                        blockquote { border-left: 4px solid #b91c1c; padding-left: 16px; background: #f8fafc; font-style: italic; }
                        img { max-width: 100%; height: auto; border-radius: 8px; }
                    `,
                    setup: (editor) => {
                        editor.ui.registry.addButton('ciomag_media', {
                            text: 'Médiathèque & Crop',
                            icon: 'image',
                            tooltip: 'Insérer ou recadrer une image depuis la médiathèque CIOMAG',
                            onAction: () => {
                                if (typeof window.openMediaManager === 'function') {
                                    window.openMediaManager({
                                        onSelect: (media) => {
                                            const captionHtml = media.caption
                                                ? `<figcaption style="font-size:12px;color:#64748b;text-align:center;margin-top:6px;">${media.caption}</figcaption>`
                                                : '';
                                            editor.insertContent(
                                                `<figure class="my-4"><img src="${media.url}" alt="${media.alt_text || media.file_name}" />${captionHtml}</figure>`
                                            );
                                        }
                                    });
                                }
                            }
                        });

                        editor.on('change keyup', () => {
                            editor.save();
                        });
                    }
                });
            },

            initGrapesJS() {
                if (!window.grapesjs || this.gjsEditor) return;

                const textarea = document.getElementById('tinymce-content');
                const initialHtml = textarea.value || '<section style="padding: 40px 20px;"><h2>Titre de la section</h2><p>Rédigez le contenu de votre page statique CIOMAG...</p></section>';

                this.gjsEditor = window.grapesjs.init({
                    container: '#gjs-page-builder',
                    height: '620px',
                    width: 'auto',
                    fromElement: false,
                    storageManager: false,
                    plugins: ['gjs-blocks-basic'],
                    pluginsOpts: {
                        'gjs-blocks-basic': { flexGrid: true }
                    },
                    canvas: {
                        styles: [
                            'https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap'
                        ]
                    }
                });

                this.gjsEditor.setComponents(initialHtml);

                // Blocs sur-mesure Magazine CIOMAG
                const bm = this.gjsEditor.BlockManager;
                bm.add('ciomag-hero', {
                    label: 'Hero Magazine',
                    category: 'Blocs CIOMAG',
                    content: `
                        <section style="background:#0f172a;color:#ffffff;padding:48px 32px;border-radius:16px;margin-bottom:24px;">
                            <span style="background:#b91c1c;color:#fff;padding:4px 10px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1px;">Édition Spéciale</span>
                            <h2 style="font-size:32px;font-weight:800;margin-top:16px;color:#ffffff;">Titre de la Page Éditoriale</h2>
                            <p style="color:#cbd5e1;font-size:17px;max-width:680px;margin-top:10px;">Présentez ici la vision, les engagements ou les dossiers spéciaux de la rédaction CIOMAG.</p>
                        </section>
                    `
                });

                bm.add('ciomag-quote', {
                    label: 'Citation Éditoriale',
                    category: 'Blocs CIOMAG',
                    content: `
                        <blockquote style="border-left:4px solid #b91c1c;background:#f8fafc;padding:20px 24px;margin:24px 0;font-style:italic;color:#1e293b;border-radius:0 8px 8px 0;">
                            « L'indépendance journalistique et l'expertise technologique sont au cœur de l'ADN de CIOMAG. »
                        </blockquote>
                    `
                });

                bm.add('ciomag-cards-2', {
                    label: 'Grille 2 Cartes',
                    category: 'Blocs CIOMAG',
                    content: `
                        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:20px;margin:24px 0;">
                            <div style="border:1px solid #e2e8f0;border-radius:12px;padding:24px;background:#ffffff;">
                                <h3 style="font-size:18px;font-weight:700;color:#0f172a;margin-bottom:8px;">Notre Mission</h3>
                                <p style="font-size:14px;color:#475569;">Accompagner les DSI, RSSI et décideurs dans leurs choix stratégiques.</p>
                            </div>
                            <div style="border:1px solid #e2e8f0;border-radius:12px;padding:24px;background:#ffffff;">
                                <h3 style="font-size:18px;font-weight:700;color:#0f172a;margin-bottom:8px;">Notre Audience</h3>
                                <p style="font-size:14px;color:#475569;">Un réseau panafricain et international de leaders du numérique.</p>
                            </div>
                        </div>
                    `
                });

                // Intégration directe du composant Media Manager CIOMAG dans GrapesJS
                this.gjsEditor.Commands.add('open-assets', {
                    run(editor, sender, opts = {}) {
                        if (typeof window.openMediaManager === 'function') {
                            window.openMediaManager({
                                onSelect: (media) => {
                                    if (opts.target) {
                                        opts.target.set('src', media.url);
                                    } else {
                                        editor.getSelected()?.set('src', media.url);
                                    }
                                }
                            });
                        }
                    }
                });

                this.gjsEditor.on('update', () => {
                    textarea.value = this.getGrapesFullHtml();
                });
            },

            getGrapesFullHtml() {
                if (!this.gjsEditor) return '';
                const html = this.gjsEditor.getHtml();
                const css = this.gjsEditor.getCss();
                return css ? `<style>${css}</style>${html}` : html;
            },

            openMediaForEditor() {
                if (typeof window.openMediaManager === 'function') {
                    window.openMediaManager({
                        onSelect: (media) => {
                            if (this.editorMode === 'tinymce' && window.tinymce?.get('tinymce-content')) {
                                window.tinymce.get('tinymce-content').insertContent(
                                    `<figure><img src="${media.url}" alt="${media.alt_text || media.file_name}" /></figure>`
                                );
                            } else if (this.editorMode === 'grapesjs' && this.gjsEditor) {
                                this.gjsEditor.addComponents(`<img src="${media.url}" alt="${media.alt_text || media.file_name}" style="max-width:100%;border-radius:12px;margin:16px 0;" />`);
                            }
                        }
                    });
                }
            },

            beforeFormSubmit() {
                const textarea = document.getElementById('tinymce-content');
                if (this.editorMode === 'tinymce' && window.tinymce?.get('tinymce-content')) {
                    window.tinymce.get('tinymce-content').save();
                } else if (this.editorMode === 'grapesjs' && this.gjsEditor) {
                    textarea.value = this.getGrapesFullHtml();
                }
            }
        };
    }
</script>
@endpush
