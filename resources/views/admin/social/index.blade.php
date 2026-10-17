@extends('layouts.admin')

@section('title', 'Réseaux Sociaux & APIs')
@section('header_title', 'Diffusion Réseaux Sociaux & Paramétrage API')

@section('content')
@php
    $postOptions = $publishedPosts->map(fn ($p) => [
        'value' => $p->id,
        'label' => $p->title . ($p->is_premium ? ' [PREMIUM]' : ''),
    ])->values()->all();

    $podcastOptions = $publishedPodcasts->map(fn ($ep) => [
        'value' => $ep->id,
        'label' => 'S' . str_pad((string) $ep->season_number, 2, '0', STR_PAD_LEFT) . 'E' . str_pad((string) $ep->episode_number, 2, '0', STR_PAD_LEFT) . ' — ' . $ep->title,
    ])->values()->all();
@endphp

<div class="space-y-6">

    {{-- Grille supérieure : Diffusion directe + Paramétrage API --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        {{-- Console de publication immédiate --}}
        <div class="lg:col-span-5 admin-card p-5 sm:p-6 space-y-4">
            <div class="border-b border-slate-100 pb-3">
                <div class="admin-badge badge-primary mb-1.5 inline-flex items-center gap-1">
                    <i data-lucide="send" class="w-3 h-3"></i>
                    <span>Diffusion Directe</span>
                </div>
                <h2 class="text-sm font-semibold text-slate-900">Publier sur Facebook, LinkedIn & X/Twitter</h2>
                <p class="text-xs text-slate-500 mt-0.5">Diffusez simultanément sur vos pages officielles.</p>
            </div>

            <form
                action="{{ route('admin.social.publish') }}"
                method="POST"
                x-data="{ contentType: 'post' }"
                class="space-y-4"
            >
                @csrf

                <input type="hidden" name="content_type" :value="contentType">

                <div class="grid grid-cols-2 gap-1.5 p-1 bg-slate-100 rounded-lg">
                    <button
                        type="button"
                        @click="contentType = 'post'"
                        :class="contentType === 'post' ? 'bg-white text-slate-900 shadow-2xs' : 'text-slate-600 hover:text-slate-900'"
                        class="py-1.5 px-3 rounded-md text-xs font-medium transition flex items-center justify-center gap-1.5 cursor-pointer"
                    >
                        <i data-lucide="file-text" class="w-3.5 h-3.5 text-red-700"></i>
                        <span>Article</span>
                    </button>
                    <button
                        type="button"
                        @click="contentType = 'podcast'"
                        :class="contentType === 'podcast' ? 'bg-white text-slate-900 shadow-2xs' : 'text-slate-600 hover:text-slate-900'"
                        class="py-1.5 px-3 rounded-md text-xs font-medium transition flex items-center justify-center gap-1.5 cursor-pointer"
                    >
                        <i data-lucide="video" class="w-3.5 h-3.5 text-red-700"></i>
                        <span>Podcast</span>
                    </button>
                </div>

                <div x-show="contentType === 'post'">
                    <x-ui.select
                        name="post_id"
                        label="Choisir l'article à partager"
                        :value="$publishedPosts->first()?->id ?? ''"
                        :options="$postOptions"
                        :searchable="true"
                    />
                </div>

                <div x-show="contentType === 'podcast'" style="display: none;">
                    <x-ui.select
                        name="podcast_id"
                        label="Choisir l'épisode de podcast à partager"
                        :value="$publishedPodcasts->first()?->id ?? ''"
                        :options="$podcastOptions"
                        :searchable="true"
                    />
                </div>

                <div>
                    <label class="admin-label">Réseaux sociaux cibles *</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <div class="p-2.5 rounded-lg border border-slate-200 bg-slate-50/70">
                            <x-ui.checkbox
                                name="platforms[]"
                                value="facebook"
                                :checked="true"
                                label="Facebook Page API"
                                :description="($config['facebook']['page_name'] ?? 'CIOMAG')"
                            />
                        </div>
                        <div class="p-2.5 rounded-lg border border-slate-200 bg-slate-50/70">
                            <x-ui.checkbox
                                name="platforms[]"
                                value="linkedin"
                                :checked="true"
                                label="LinkedIn UGC API"
                                :description="($config['linkedin']['organization_name'] ?? 'CIOMAG')"
                            />
                        </div>
                        <div class="p-2.5 rounded-lg border border-slate-200 bg-slate-50/70">
                            <x-ui.checkbox
                                name="platforms[]"
                                value="twitter"
                                :checked="true"
                                label="X / Twitter API v2"
                                :description="($config['twitter']['handle'] ?? '@CIOMAG')"
                            />
                        </div>
                        <div class="p-2.5 rounded-lg border border-slate-200 bg-slate-50/70">
                            <x-ui.checkbox
                                name="platforms[]"
                                value="youtube"
                                :checked="false"
                                label="YouTube Video"
                                :description="($config['youtube']['channel_name'] ?? 'CIOMAG TV')"
                            />
                        </div>
                    </div>
                </div>

                <div>
                    <label class="admin-label">Message d'accompagnement (Optionnel)</label>
                    <textarea
                        name="custom_message"
                        rows="3"
                        placeholder="Laissez vide pour utiliser le modèle automatique ({title} - {excerpt} {url})..."
                        class="admin-input font-mono text-xs"
                    ></textarea>
                </div>

                <button type="submit" class="w-full btn btn-md btn-primary justify-center">
                    <i data-lucide="share-2" class="w-4 h-4"></i>
                    <span>Diffuser maintenant via API</span>
                </button>
            </form>
        </div>

        {{-- Paramétrage des clés API --}}
        <div class="lg:col-span-7 admin-card p-5 sm:p-6 space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-3">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900 flex items-center gap-2">
                        <i data-lucide="key-round" class="w-4 h-4 text-red-700"></i>
                        <span>Paramétrage des Connecteurs API Réseaux Sociaux</span>
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Tokens d'accès Graph API, LinkedIn UGC, X/Twitter et YouTube.</p>
                </div>
                <span class="admin-badge {{ ($config['mode'] ?? 'sandbox') === 'live' ? 'badge-success' : 'badge-warning' }}">
                    Mode : {{ ($config['mode'] ?? 'sandbox') === 'live' ? 'Production Live' : 'Sandbox (Test)' }}
                </span>
            </div>

            <form action="{{ route('admin.social.config.update') }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 p-3.5 rounded-lg bg-slate-50 border border-slate-200">
                    <x-ui.select
                        name="mode"
                        label="Mode d'exécution des appels API"
                        :value="$config['mode'] ?? 'sandbox'"
                        :options="[
                            ['value' => 'sandbox', 'label' => 'Sandbox (Simulation & log)'],
                            ['value' => 'live', 'label' => 'Production Live (Appels réels)'],
                        ]"
                    />

                    <div class="flex items-end pb-1">
                        <x-ui.checkbox
                            name="auto_publish_on_post"
                            value="1"
                            :checked="(bool) ($config['auto_publish_on_post'] ?? false)"
                            label="Auto-diffusion à la publication"
                        />
                    </div>

                    <div class="md:col-span-2">
                        <label class="admin-label">Modèle de message par défaut</label>
                        <input
                            type="text"
                            name="default_template"
                            value="{{ $config['default_template'] ?? '📰 {title} — {excerpt} 👉 {url} #CIOMAG #Afrique #Tech' }}"
                            class="admin-input font-mono text-xs"
                        >
                        <span class="admin-help-text">Variables : <code>{title}</code>, <code>{excerpt}</code>, <code>{url}</code></span>
                    </div>
                </div>

                {{-- Cartes de configuration par réseau --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">

                    {{-- Facebook Graph API --}}
                    <div class="p-3.5 rounded-lg border border-slate-200 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-blue-700 flex items-center gap-1.5">
                                <i data-lucide="facebook" class="w-3.5 h-3.5"></i> Facebook
                            </span>
                            <x-ui.checkbox name="fb_enabled" value="1" :checked="(bool) ($config['facebook']['enabled'] ?? true)" label="Actif" />
                        </div>
                        <input type="text" name="fb_page_name" value="{{ $config['facebook']['page_name'] ?? '' }}" placeholder="Nom Page (CIOMAG)" class="admin-input">
                        <div class="grid grid-cols-3 gap-2">
                            <input type="text" name="fb_page_id" value="{{ $config['facebook']['page_id'] ?? '' }}" placeholder="Page ID" class="col-span-2 admin-input font-mono text-xs">
                            <input type="text" name="fb_api_version" value="{{ $config['facebook']['api_version'] ?? 'v21.0' }}" placeholder="v21.0" class="admin-input font-mono text-xs">
                        </div>
                        <input type="password" name="fb_access_token" value="{{ $config['facebook']['access_token'] ?? '' }}" placeholder="Access Token (EAAG...)" class="admin-input font-mono text-xs">
                    </div>

                    {{-- LinkedIn UGC API --}}
                    <div class="p-3.5 rounded-lg border border-slate-200 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-sky-700 flex items-center gap-1.5">
                                <i data-lucide="linkedin" class="w-3.5 h-3.5"></i> LinkedIn
                            </span>
                            <x-ui.checkbox name="li_enabled" value="1" :checked="(bool) ($config['linkedin']['enabled'] ?? true)" label="Actif" />
                        </div>
                        <input type="text" name="li_organization_name" value="{{ $config['linkedin']['organization_name'] ?? '' }}" placeholder="Nom Organisation" class="admin-input">
                        <input type="text" name="li_organization_urn" value="{{ $config['linkedin']['organization_urn'] ?? '' }}" placeholder="urn:li:organization:..." class="admin-input font-mono text-xs">
                        <input type="password" name="li_access_token" value="{{ $config['linkedin']['access_token'] ?? '' }}" placeholder="Access Token (AQV...)" class="admin-input font-mono text-xs">
                    </div>

                    {{-- X / Twitter API v2 --}}
                    <div class="p-3.5 rounded-lg border border-slate-200 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-slate-800 flex items-center gap-1.5">
                                <i data-lucide="twitter" class="w-3.5 h-3.5"></i> X / Twitter v2
                            </span>
                            <x-ui.checkbox name="tw_enabled" value="1" :checked="(bool) ($config['twitter']['enabled'] ?? true)" label="Actif" />
                        </div>
                        <input type="text" name="tw_handle" value="{{ $config['twitter']['handle'] ?? '' }}" placeholder="@CIOMAG" class="admin-input">
                        <div class="grid grid-cols-2 gap-2">
                            <input type="text" name="tw_api_key" value="{{ $config['twitter']['api_key'] ?? '' }}" placeholder="API Key" class="admin-input font-mono text-xs">
                            <input type="password" name="tw_api_secret" value="{{ $config['twitter']['api_secret'] ?? '' }}" placeholder="API Secret" class="admin-input font-mono text-xs">
                        </div>
                        <input type="password" name="tw_bearer_token" value="{{ $config['twitter']['bearer_token'] ?? '' }}" placeholder="Bearer Token" class="admin-input font-mono text-xs">
                    </div>

                    {{-- YouTube Data API v3 --}}
                    <div class="p-3.5 rounded-lg border border-slate-200 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-red-700 flex items-center gap-1.5">
                                <i data-lucide="youtube" class="w-3.5 h-3.5"></i> YouTube Data API
                            </span>
                            <x-ui.checkbox name="yt_enabled" value="1" :checked="(bool) ($config['youtube']['enabled'] ?? true)" label="Actif" />
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <input type="text" name="yt_channel_name" value="{{ $config['youtube']['channel_name'] ?? '' }}" placeholder="CIOMAG TV" class="admin-input">
                            <input type="text" name="yt_channel_id" value="{{ $config['youtube']['channel_id'] ?? '' }}" placeholder="Channel ID" class="admin-input font-mono text-xs">
                        </div>
                        <input type="password" name="yt_api_key" value="{{ $config['youtube']['api_key'] ?? '' }}" placeholder="API Key" class="admin-input font-mono text-xs">
                        <div class="grid grid-cols-2 gap-2">
                            <input type="text" name="yt_client_id" value="{{ $config['youtube']['client_id'] ?? '' }}" placeholder="Client ID" class="admin-input font-mono text-xs">
                            <input type="password" name="yt_client_secret" value="{{ $config['youtube']['client_secret'] ?? '' }}" placeholder="Client Secret" class="admin-input font-mono text-xs">
                        </div>
                    </div>

                </div>

                <div class="flex justify-end pt-1">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        <span>Enregistrer la configuration API</span>
                    </button>
                </div>
            </form>
        </div>

    </div>

    {{-- Journal historique des diffusions --}}
    <div class="admin-table-container">
        <div class="px-5 py-3.5 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-sm font-semibold text-slate-900 flex items-center gap-2">
                    <i data-lucide="history" class="w-4 h-4 text-red-700"></i>
                    <span>Historique des Diffusions Réseaux Sociaux</span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Traçabilité des partages via Facebook, LinkedIn, X/Twitter et YouTube.</p>
            </div>

            <div class="flex flex-wrap items-center gap-1.5">
                <a href="{{ route('admin.social.index') }}" class="px-2.5 py-1 rounded-lg text-xs font-medium {{ !$platform ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">Tous</a>
                @foreach(['facebook' => 'Facebook', 'linkedin' => 'LinkedIn', 'twitter' => 'X / Twitter', 'youtube' => 'YouTube'] as $pKey => $pLabel)
                    <a href="{{ route('admin.social.index', ['platform' => $pKey]) }}" class="px-2.5 py-1 rounded-lg text-xs font-medium {{ $platform === $pKey ? 'bg-red-700 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        {{ $pLabel }}
                    </a>
                @endforeach
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Plateforme</th>
                        <th>Contenu</th>
                        <th>Message diffusé</th>
                        <th>ID Externe / Lien</th>
                        <th>Statut API</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($shares as $share)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td>
                                <span class="admin-badge badge-neutral inline-flex items-center gap-1.5">
                                    <i data-lucide="{{ $share->platformIcon() }}" class="w-3.5 h-3.5 text-red-700"></i>
                                    <span>{{ $share->platformLabel() }}</span>
                                </span>
                            </td>
                            <td class="text-xs">
                                @if($share->post)
                                    <div class="font-semibold text-slate-900 line-clamp-1">{{ $share->post->title }}</div>
                                    <span class="text-[11px] text-slate-400">Article</span>
                                @elseif($share->podcast)
                                    <div class="font-semibold text-slate-900 line-clamp-1">{{ $share->podcast->title }}</div>
                                    <span class="text-[11px] text-red-700 font-medium">Podcast</span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="text-xs text-slate-600 max-w-xs">
                                <div class="line-clamp-2">{{ $share->message }}</div>
                            </td>
                            <td class="text-xs font-mono">
                                @if($share->external_url)
                                    <a href="{{ $share->external_url }}" target="_blank" class="text-red-700 hover:underline inline-flex items-center gap-1">
                                        <span>{{ \Illuminate\Support\Str::limit($share->external_post_id ?: 'Voir post', 20) }}</span>
                                        <i data-lucide="external-link" class="w-3 h-3"></i>
                                    </a>
                                @else
                                    <span class="text-slate-400">{{ $share->external_post_id ?: '—' }}</span>
                                @endif
                            </td>
                            <td>
                                @if($share->status === 'published')
                                    <span class="admin-badge badge-success">Diffusé (Live)</span>
                                @elseif($share->status === 'simulated')
                                    <span class="admin-badge badge-info">Diffusé (Sandbox)</span>
                                @elseif($share->status === 'failed')
                                    <span class="admin-badge badge-danger" title="{{ $share->error_message }}">Échec</span>
                                @else
                                    <span class="admin-badge badge-warning">{{ ucfirst($share->status) }}</span>
                                @endif
                            </td>
                            <td class="text-xs text-slate-500">
                                {{ $share->shared_at?->diffForHumans() ?? $share->created_at?->diffForHumans() }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400 text-xs">Aucune publication sociale enregistrée.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $shares->links() }}
        </div>
    </div>

</div>
@endsection
