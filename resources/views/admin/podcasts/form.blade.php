@extends('layouts.admin')

@section('title', $podcast->exists ? 'Modifier le podcast' : 'Nouvel épisode de podcast')
@section('header_title', $podcast->exists ? 'Éditer : ' . $podcast->title : 'Ajouter un épisode Podcast / Vidéo')

@section('top_actions')
    <a href="{{ route('admin.podcasts.index') }}" class="px-3.5 py-2 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition flex items-center gap-1.5">
        <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
        <span>Retour aux podcasts</span>
    </a>
    @if($podcast->exists)
        <a href="{{ route('magazine.podcasts.show', $podcast->slug) }}" target="_blank" class="px-3.5 py-2 rounded-lg border border-red-200 bg-red-50 hover:bg-red-100 text-red-700 text-xs font-bold transition flex items-center gap-1.5">
            <i data-lucide="play" class="w-3.5 h-3.5"></i>
            <span>Voir le lecteur public</span>
        </a>
    @endif
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

        {{-- Source Vidéo : YouTube ou Téléversement Direct sur la plateforme --}}
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-2xs space-y-5">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-3">
                <div>
                    <h2 class="text-sm font-black uppercase tracking-wider text-slate-900 flex items-center gap-2">
                        <i data-lucide="tv" class="w-4 h-4 text-red-700"></i>
                        <span>Source de la Vidéo / Épisode</span>
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Importez une vidéo depuis YouTube ou chargez un fichier vidéo directement sur la plateforme CIOMAG.</p>
                </div>
            </div>

            <input type="hidden" name="source_type" :value="sourceType">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <button
                    type="button"
                    @click="sourceType = 'youtube'"
                    :class="sourceType === 'youtube' ? 'border-red-700 bg-red-50/60 ring-2 ring-red-700/20' : 'border-slate-200 bg-slate-50 hover:bg-slate-100'"
                    class="p-4 rounded-xl border text-left transition flex items-start gap-3 cursor-pointer"
                >
                    <div class="w-10 h-10 rounded-xl bg-red-600 text-white flex items-center justify-center shrink-0">
                        <i data-lucide="youtube" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <div class="text-xs font-black uppercase tracking-wider text-slate-900">Import YouTube</div>
                        <p class="text-[11px] text-slate-500 mt-0.5">Extraction automatique de l'ID vidéo, miniature HD et lecteur intégré.</p>
                    </div>
                </button>

                <button
                    type="button"
                    @click="sourceType = 'upload'"
                    :class="sourceType === 'upload' ? 'border-indigo-700 bg-indigo-50/60 ring-2 ring-indigo-700/20' : 'border-slate-200 bg-slate-50 hover:bg-slate-100'"
                    class="p-4 rounded-xl border text-left transition flex items-start gap-3 cursor-pointer"
                >
                    <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center shrink-0">
                        <i data-lucide="upload-cloud" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <div class="text-xs font-black uppercase tracking-wider text-slate-900">Chargement Direct Plateforme</div>
                        <p class="text-[11px] text-slate-500 mt-0.5">Téléversez un fichier MP4/WebM ou indiquez un flux vidéo hébergé sur CIOMAG.</p>
                    </div>
                </button>
            </div>

            {{-- Panneau YouTube --}}
            <div x-show="sourceType === 'youtube'" class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-3">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">URL de la vidéo YouTube</label>
                <input
                    type="text"
                    name="youtube_url"
                    x-model="youtubeUrl"
                    placeholder="Ex: https://www.youtube.com/watch?v=M7lc1UVf-VE ou https://youtu.be/M7lc1UVf-VE"
                    class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm focus:border-red-700 focus:outline-none"
                >
                <p class="text-[11px] text-slate-500">L'identifiant YouTube et la miniature haute définition seront extraits automatiquement à l'enregistrement.</p>
            </div>

            {{-- Panneau Upload Direct --}}
            <div x-show="sourceType === 'upload'" style="display: none;" class="p-4 rounded-xl bg-indigo-50/40 border border-indigo-200 space-y-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Téléverser un fichier vidéo (.mp4, .webm, .mov)</label>
                    <input
                        type="file"
                        name="video_file"
                        accept="video/mp4,video/webm,video/quicktime"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs"
                    >
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Ou chemin / URL directe du fichier vidéo sur la plateforme</label>
                    <input
                        type="text"
                        name="video_file_path"
                        value="{{ old('video_file_path', $podcast->video_file_path) }}"
                        placeholder="Ex: https://cdn.ciomag.com/podcasts/episode-04.mp4 ou podcasts/videos/ep4.mp4"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm"
                    >
                </div>
            </div>
        </div>

        {{-- Informations éditoriales de l'épisode --}}
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-2xs space-y-5">
            <h2 class="text-sm font-black uppercase tracking-wider text-slate-900 flex items-center gap-2 border-b border-slate-100 pb-3">
                <i data-lucide="mic" class="w-4 h-4 text-red-700"></i>
                <span>Informations de l'épisode & Invité</span>
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Titre de l'épisode *</label>
                    <input type="text" name="title" value="{{ old('title', $podcast->title) }}" required placeholder="Ex: Cybersécurité bancaire : comment parer aux menaces IA ?" class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm focus:border-red-700 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Saison #</label>
                    <input type="number" name="season_number" value="{{ old('season_number', $podcast->season_number ?? 2) }}" min="1" class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Épisode #</label>
                    <input type="number" name="episode_number" value="{{ old('episode_number', $podcast->episode_number ?? 1) }}" min="1" class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Nom de l'invité</label>
                    <input type="text" name="guest_name" value="{{ old('guest_name', $podcast->guest_name) }}" placeholder="Ex: Dr. Seydou Koné" class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Fonction / Entreprise de l'invité</label>
                    <input type="text" name="guest_role" value="{{ old('guest_role', $podcast->guest_role) }}" placeholder="Ex: RSSI Groupe Banque Atlantique" class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Journaliste / Animateur</label>
                    <input type="text" name="host_name" value="{{ old('host_name', $podcast->host_name ?? 'Rédaction CIOMAG') }}" class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Accroche / Résumé court</label>
                <textarea name="excerpt" rows="2" class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm" placeholder="Résumé de l'entretien...">{{ old('excerpt', $podcast->excerpt) }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Notes d'émission & Chapitrage (HTML autorisé)</label>
                <textarea name="description" rows="5" class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm" placeholder="<p>Points abordés dans cet épisode...</p>">{{ old('description', $podcast->description) }}</textarea>
            </div>
        </div>

        {{-- Préparation API Multi-Diffusion Vidéo (YouTube, Facebook, Twitter/X, LinkedIn) --}}
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-2xs space-y-4">
            <div class="border-b border-slate-100 pb-3">
                <h2 class="text-sm font-black uppercase tracking-wider text-slate-900 flex items-center gap-2">
                    <i data-lucide="share-2" class="w-4 h-4 text-red-700"></i>
                    <span>Passerelle API Multi-Diffusion Vidéo & Réseaux Sociaux</span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Sélectionnez les réseaux cibles prêts à recevoir ou synchroniser cet épisode via les connecteurs API CIOMAG.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50/70">
                    <x-ui.checkbox
                        name="dist_youtube"
                        value="1"
                        :checked="(bool) ($dist['youtube']['enabled'] ?? true)"
                        label="YouTube Data API v3"
                        description="Synchronisation chaîne YouTube officielle"
                    />
                </div>
                <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50/70">
                    <x-ui.checkbox
                        name="dist_facebook"
                        value="1"
                        :checked="(bool) ($dist['facebook']['enabled'] ?? true)"
                        label="Facebook Graph Video API"
                        description="Publication native sur la page Facebook"
                    />
                </div>
                <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50/70">
                    <x-ui.checkbox
                        name="dist_linkedin"
                        value="1"
                        :checked="(bool) ($dist['linkedin']['enabled'] ?? true)"
                        label="LinkedIn Organization Video API"
                        description="Diffusion sur la page entreprise LinkedIn"
                    />
                </div>
                <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50/70">
                    <x-ui.checkbox
                        name="dist_twitter"
                        value="1"
                        :checked="(bool) ($dist['twitter']['enabled'] ?? true)"
                        label="X / Twitter Media API v2"
                        description="Post vidéo avec lien lecteur CIOMAG"
                    />
                </div>
            </div>
        </div>

    </div>

    {{-- Colonne latérale --}}
    <div class="lg:col-span-4 space-y-6">

        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-2xs space-y-4">
            <h3 class="text-xs font-black uppercase tracking-wider text-slate-900 flex items-center gap-2">
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
                    <label class="block text-xs font-bold text-slate-700 mb-1">Durée (MM:SS)</label>
                    <input type="text" name="duration_label" value="{{ old('duration_label', $podcast->duration_label ?? '35:00') }}" placeholder="38:20" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Slug URL</label>
                    <input type="text" name="slug" value="{{ old('slug', $podcast->slug) }}" placeholder="Auto" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs font-mono">
                </div>
            </div>

            <div class="pt-2 border-t border-slate-100">
                <x-ui.checkbox
                    name="is_featured"
                    value="1"
                    :checked="(bool) old('is_featured', $podcast->is_featured ?? false)"
                    label="Épisode en Vedette (Hero Studio)"
                    description="Afficher en grand lecteur sur la page Podcasts"
                />
            </div>

            <button type="submit" class="w-full py-3 px-4 rounded-xl bg-red-700 hover:bg-red-800 text-white font-bold text-xs uppercase tracking-wider transition shadow-sm flex items-center justify-center gap-2 cursor-pointer">
                <i data-lucide="save" class="w-4 h-4"></i>
                <span>{{ $podcast->exists ? 'Mettre à jour l\'épisode' : 'Publier l\'épisode' }}</span>
            </button>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-2xs space-y-3">
            <h3 class="text-xs font-black uppercase tracking-wider text-slate-900 flex items-center gap-2">
                <i data-lucide="image" class="w-4 h-4 text-red-700"></i>
                <span>Vignette / Miniature personnalisée</span>
            </h3>
            <p class="text-[11px] text-slate-500">Optionnel pour YouTube (la miniature officielle YouTube est récupérée automatiquement si vide).</p>

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
