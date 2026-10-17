@extends('layouts.admin')

@section('title', $podcast->exists ? 'Modifier le podcast' : 'Nouvel épisode de podcast')
@section('header_title', $podcast->exists ? 'Éditer : ' . $podcast->title : 'Ajouter un épisode Podcast / Vidéo')

@section('top_actions')
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.podcasts.index') }}" class="btn btn-sm btn-secondary">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
            <span>Retour aux podcasts</span>
        </a>
        @if($podcast->exists)
            <a href="{{ route('magazine.podcasts.show', $podcast->slug) }}" target="_blank" class="btn btn-sm btn-secondary">
                <i data-lucide="play" class="w-3.5 h-3.5 text-slate-500"></i>
                <span>Lecteur public</span>
            </a>
        @endif
    </div>
@endsection

@section('content')
@php
    $categoryOptions = collect([['value' => '', 'label' => 'Aucune catégorie']])
        ->concat($categories->map(fn ($c) => ['value' => $c->id, 'label' => $c->name]))
        ->values()
        ->all();
    $dist = $podcast->social_distribution ?? [];
@endphp

<form
    action="{{ $podcast->exists ? route('admin.podcasts.update', $podcast) : route('admin.podcasts.store') }}"
    method="POST"
    enctype="multipart/form-data"
    x-data="{
        sourceType: @js(old('source_type', $podcast->source_type ?? 'youtube')),
        youtubeUrl: @js(old('youtube_url', $podcast->youtube_url ?? ''))
    }"
    class="grid grid-cols-1 lg:grid-cols-12 gap-6"
>
    @csrf
    @if($podcast->exists)
        @method('PUT')
    @endif

    {{-- Colonne principale --}}
    <div class="lg:col-span-8 space-y-6">

        {{-- Source Vidéo : YouTube ou Téléversement Direct --}}
        <div class="admin-card p-5 sm:p-6 space-y-4">
            <div class="border-b border-slate-100 pb-3">
                <h2 class="text-sm font-semibold text-slate-900 flex items-center gap-2">
                    <i data-lucide="tv" class="w-4 h-4 text-red-700"></i>
                    <span>Source de la Vidéo / Épisode</span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Importez une vidéo YouTube ou chargez un fichier vidéo direct.</p>
            </div>

            <input type="hidden" name="source_type" :value="sourceType">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <button
                    type="button"
                    @click="sourceType = 'youtube'"
                    :class="sourceType === 'youtube' ? 'border-red-700 bg-red-50/40 ring-1 ring-red-700/20' : 'border-slate-200 bg-white hover:bg-slate-50'"
                    class="p-4 rounded-xl border text-left transition flex items-start gap-3 cursor-pointer"
                >
                    <div class="w-9 h-9 rounded-lg bg-red-600 text-white flex items-center justify-center shrink-0">
                        <i data-lucide="youtube" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-slate-900">Import YouTube</div>
                        <p class="text-[11px] text-slate-500 mt-0.5">Miniature HD, extraction ID et player intégré.</p>
                    </div>
                </button>

                <button
                    type="button"
                    @click="sourceType = 'upload'"
                    :class="sourceType === 'upload' ? 'border-red-700 bg-red-50/40 ring-1 ring-red-700/20' : 'border-slate-200 bg-white hover:bg-slate-50'"
                    class="p-4 rounded-xl border text-left transition flex items-start gap-3 cursor-pointer"
                >
                    <div class="w-9 h-9 rounded-lg bg-slate-900 text-white flex items-center justify-center shrink-0">
                        <i data-lucide="upload-cloud" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-slate-900">Vidéo Hébergée</div>
                        <p class="text-[11px] text-slate-500 mt-0.5">Téléversez un MP4/WebM sur la plateforme CIOMAG.</p>
                    </div>
                </button>
            </div>

            {{-- Panneau YouTube --}}
            <div x-show="sourceType === 'youtube'" class="p-4 rounded-lg bg-slate-50 border border-slate-200 space-y-2">
                <label class="admin-label">URL de la vidéo YouTube</label>
                <input
                    type="text"
                    name="youtube_url"
                    x-model="youtubeUrl"
                    placeholder="https://www.youtube.com/watch?v=..."
                    class="admin-input"
                >
                <p class="admin-help-text">Miniature et ID extraits automatiquement.</p>
            </div>

            {{-- Panneau Upload Direct --}}
            <div x-show="sourceType === 'upload'" style="display: none;" class="p-4 rounded-lg bg-slate-50 border border-slate-200 space-y-3">
                <div>
                    <label class="admin-label">Téléverser un fichier vidéo (.mp4, .webm)</label>
                    <input
                        type="file"
                        name="video_file"
                        accept="video/mp4,video/webm,video/quicktime"
                        class="admin-input text-xs"
                    >
                </div>

                <div>
                    <label class="admin-label">Ou URL directe du fichier vidéo</label>
                    <input
                        type="text"
                        name="video_file_path"
                        value="{{ old('video_file_path', $podcast->video_file_path) }}"
                        placeholder="https://cdn.ciomag.com/..."
                        class="admin-input"
                    >
                </div>
            </div>
        </div>

        {{-- Informations éditoriales de l'épisode --}}
        <div class="admin-card p-5 sm:p-6 space-y-4">
            <h2 class="text-sm font-semibold text-slate-900 flex items-center gap-2 border-b border-slate-100 pb-3">
                <i data-lucide="mic" class="w-4 h-4 text-red-700"></i>
                <span>Informations de l'épisode & Invité</span>
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-2">
                    <label class="admin-label">Titre de l'épisode *</label>
                    <input type="text" name="title" value="{{ old('title', $podcast->title) }}" required placeholder="Ex: Cybersécurité bancaire : comment parer aux menaces IA ?" class="admin-input font-medium">
                </div>
                <div>
                    <label class="admin-label">Saison #</label>
                    <input type="number" name="season_number" value="{{ old('season_number', $podcast->season_number ?? 2) }}" min="1" class="admin-input">
                </div>
                <div>
                    <label class="admin-label">Épisode #</label>
                    <input type="number" name="episode_number" value="{{ old('episode_number', $podcast->episode_number ?? 1) }}" min="1" class="admin-input">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="admin-label">Nom de l'invité</label>
                    <input type="text" name="guest_name" value="{{ old('guest_name', $podcast->guest_name) }}" placeholder="Ex: Dr. Seydou Koné" class="admin-input">
                </div>
                <div>
                    <label class="admin-label">Fonction de l'invité</label>
                    <input type="text" name="guest_role" value="{{ old('guest_role', $podcast->guest_role) }}" placeholder="Ex: RSSI Groupe Banque Atlantique" class="admin-input">
                </div>
                <div>
                    <label class="admin-label">Animateur</label>
                    <input type="text" name="host_name" value="{{ old('host_name', $podcast->host_name ?? 'Rédaction CIOMAG') }}" class="admin-input">
                </div>
            </div>

            <div>
                <label class="admin-label">Accroche / Résumé court</label>
                <textarea name="excerpt" rows="2" class="admin-input" placeholder="Résumé de l'entretien...">{{ old('excerpt', $podcast->excerpt) }}</textarea>
            </div>

            <div>
                <label class="admin-label">Notes d'émission (HTML autorisé)</label>
                <textarea name="description" rows="5" class="admin-input font-mono text-xs" placeholder="<p>Points abordés...</p>">{{ old('description', $podcast->description) }}</textarea>
            </div>
        </div>

        {{-- Multi-Diffusion Réseaux Sociaux --}}
        <div
            x-data="{
                targetNames: ['dist_youtube', 'dist_facebook', 'dist_linkedin', 'dist_twitter'],
                allChecked: false,
                updateState() {
                    const inputs = this.targetNames.map(name => document.querySelector(`input[name='${name}']`)).filter(Boolean);
                    this.allChecked = inputs.length > 0 && inputs.every(i => i.checked);
                },
                setAll(checked) {
                    this.$dispatch('set-checked', {
                        names: this.targetNames,
                        checked: checked
                    });
                    this.targetNames.forEach(name => {
                        const input = document.querySelector(`input[name='${name}']`);
                        if (input) {
                            input.checked = checked;
                            input.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                    });
                    this.allChecked = checked;
                }
            }"
            x-init="$nextTick(() => updateState())"
            @change="updateState()"
            class="admin-card p-5 sm:p-6 space-y-4"
        >
            <div class="border-b border-slate-100 pb-3 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900 flex items-center gap-2">
                        <i data-lucide="share-2" class="w-4 h-4 text-red-700"></i>
                        <span>Multi-Diffusion Vidéo Réseaux Sociaux</span>
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Réseaux cibles prêts à synchroniser cet épisode via API.</p>
                </div>

                {{-- Option dynamique : un seul bouton visible à la fois --}}
                <div>
                    <button
                        type="button"
                        x-show="!allChecked"
                        @click="setAll(true)"
                        class="btn btn-sm btn-secondary inline-flex items-center gap-1.5"
                        title="Cocher tous les réseaux de diffusion"
                    >
                        <i data-lucide="check-check" class="w-3.5 h-3.5 text-primary"></i>
                        <span>Tout cocher</span>
                    </button>
                    <button
                        type="button"
                        x-show="allChecked"
                        style="display: none;"
                        @click="setAll(false)"
                        class="btn btn-sm btn-secondary text-slate-600 hover:text-slate-900 inline-flex items-center gap-1.5"
                        title="Décocher tous les réseaux de diffusion"
                    >
                        <i data-lucide="square" class="w-3.5 h-3.5 text-slate-400"></i>
                        <span>Tout décocher</span>
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="p-3 rounded-lg border border-slate-200 bg-slate-50/70">
                    <x-ui.checkbox
                        name="dist_youtube"
                        value="1"
                        :checked="(bool) ($dist['youtube']['enabled'] ?? true)"
                        label="YouTube Data API v3"
                    />
                </div>
                <div class="p-3 rounded-lg border border-slate-200 bg-slate-50/70">
                    <x-ui.checkbox
                        name="dist_facebook"
                        value="1"
                        :checked="(bool) ($dist['facebook']['enabled'] ?? true)"
                        label="Facebook Video API"
                    />
                </div>
                <div class="p-3 rounded-lg border border-slate-200 bg-slate-50/70">
                    <x-ui.checkbox
                        name="dist_linkedin"
                        value="1"
                        :checked="(bool) ($dist['linkedin']['enabled'] ?? true)"
                        label="LinkedIn Video API"
                    />
                </div>
                <div class="p-3 rounded-lg border border-slate-200 bg-slate-50/70">
                    <x-ui.checkbox
                        name="dist_twitter"
                        value="1"
                        :checked="(bool) ($dist['twitter']['enabled'] ?? true)"
                        label="X / Twitter API v2"
                    />
                </div>
            </div>
        </div>

    </div>

    {{-- Colonne latérale --}}
    <div class="lg:col-span-4 space-y-6">

        <div class="admin-card p-5 sm:p-6 space-y-4">
            <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-700 flex items-center gap-2 border-b border-slate-100 pb-2.5">
                <i data-lucide="settings" class="w-4 h-4 text-red-700"></i>
                <span>Publication & Rubrique</span>
            </h3>

            <x-ui.select
                name="status"
                label="Statut de l'épisode"
                :value="old('status', $podcast->status ?? 'published')"
                :options="[
                    ['value' => 'published', 'label' => 'Publié'],
                    ['value' => 'draft', 'label' => 'Brouillon'],
                    ['value' => 'archived', 'label' => 'Archivé'],
                ]"
            />

            <x-ui.select
                name="category_id"
                label="Rubrique thématique"
                :value="old('category_id', $podcast->category_id ?? '')"
                :options="$categoryOptions"
                :searchable="true"
            />

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="admin-label">Durée (MM:SS)</label>
                    <input type="text" name="duration_label" value="{{ old('duration_label', $podcast->duration_label ?? '35:00') }}" placeholder="38:20" class="admin-input">
                </div>
                <div>
                    <label class="admin-label">Slug URL</label>
                    <input type="text" name="slug" value="{{ old('slug', $podcast->slug) }}" placeholder="Auto" class="admin-input font-mono text-xs">
                </div>
            </div>

            <div class="pt-2 border-t border-slate-100 flex justify-end">
                <x-ui.checkbox
                    name="is_featured"
                    value="1"
                    :checked="(bool) old('is_featured', $podcast->is_featured ?? false)"
                    label="Épisode en Vedette"
                />
            </div>

            <div class="flex justify-end">
                <button type="submit" class="w-auto btn btn-md btn-primary justify-center">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>{{ $podcast->exists ? 'Mettre à jour l\'épisode' : 'Publier l\'épisode' }}</span>
                </button>
            </div>
        </div>

        <div class="admin-card p-5 sm:p-6 space-y-3">
            <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-700 flex items-center gap-2 border-b border-slate-100 pb-2.5">
                <i data-lucide="image" class="w-4 h-4 text-red-700"></i>
                <span>Miniature personnalisée</span>
            </h3>
            <p class="admin-help-text">Optionnel pour YouTube (miniature officielle récupérée si vide).</p>

            <x-media-picker
                name="thumbnail_url"
                valueMode="url"
                :value="old('thumbnail_url', $podcast->thumbnail_url)"
                :previewUrl="old('thumbnail_url', $podcast->thumbnail_url)"
                label="Miniature de l'épisode"
            />
        </div>

    </div>
</form>
@endsection
