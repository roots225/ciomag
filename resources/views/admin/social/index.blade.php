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

<div class="space-y-8">

    {{-- Grille supérieure : Diffusion immédiate en 1 clic + Paramétrage des API --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        {{-- Console de publication immédiate d'un article ou podcast --}}
        <div class="lg:col-span-5 bg-white rounded-xl border border-slate-200 p-6 shadow-2xs space-y-5">
            <div class="border-b border-slate-100 pb-3">
                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-red-50 text-red-700 text-[11px] font-extrabold uppercase tracking-wider mb-2">
                    <i data-lucide="send" class="w-3.5 h-3.5"></i>
                    <span>Console de Diffusion Directe</span>
                </div>
                <h2 class="text-base font-black text-slate-900">Publier sur Facebook, LinkedIn & X/Twitter</h2>
                <p class="text-xs text-slate-500 mt-0.5">Sélectionnez un article ou un épisode podcast et diffusez-le simultanément via les APIs configurées.</p>
            </div>

            <form
                action="{{ route('admin.social.publish') }}"
                method="POST"
                x-data="{ contentType: 'post' }"
                class="space-y-4"
            >
                @csrf

                <input type="hidden" name="content_type" :value="contentType">

                <div class="grid grid-cols-2 gap-2 p-1 bg-slate-100 rounded-xl">
                    <button
                        type="button"
                        @click="contentType = 'post'"
                        :class="contentType === 'post' ? 'bg-white text-slate-900 shadow-2xs' : 'text-slate-600 hover:text-slate-900'"
                        class="py-2 px-3 rounded-lg text-xs font-bold transition flex items-center justify-center gap-1.5 cursor-pointer"
                    >
                        <i data-lucide="file-text" class="w-3.5 h-3.5 text-red-700"></i>
                        <span>Article Magazine</span>
                    </button>
                    <button
                        type="button"
                        @click="contentType = 'podcast'"
                        :class="contentType === 'podcast' ? 'bg-white text-slate-900 shadow-2xs' : 'text-slate-600 hover:text-slate-900'"
                        class="py-2 px-3 rounded-lg text-xs font-bold transition flex items-center justify-center gap-1.5 cursor-pointer"
                    >
                        <i data-lucide="video" class="w-3.5 h-3.5 text-red-700"></i>
                        <span>Épisode Podcast</span>
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
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Réseaux sociaux cibles *</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <div class="p-3 rounded-xl border border-slate-200 bg-slate-50/70">
                            <x-ui.checkbox
                                name="platforms[]"
                                value="facebook"
                                :checked="true"
                                label="Facebook Page API"
                                :description="($config['facebook']['page_name'] ?? 'CIOMAG Officiel')"
                            />
                        </div>
                        <div class="p-3 rounded-xl border border-slate-200 bg-slate-50/70">
                            <x-ui.checkbox
                                name="platforms[]"
                                value="linkedin"
                                :checked="true"
                                label="LinkedIn UGC API"
                                :description="($config['linkedin']['organization_name'] ?? 'CIOMAG Magazine')"
                            />
                        </div>
                        <div class="p-3 rounded-xl border border-slate-200 bg-slate-50/70">
                            <x-ui.checkbox
                                name="platforms[]"
                                value="twitter"
                                :checked="true"
                                label="X / Twitter API v2"
                                :description="($config['twitter']['handle'] ?? '@CIOMAG_Afrique')"
                            />
                        </div>
                        <div class="p-3 rounded-xl border border-slate-200 bg-slate-50/70">
                            <x-ui.checkbox
                                name="platforms[]"
                                value="youtube"
                                :checked="false"
                                label="YouTube Community / Video"
                                :description="($config['youtube']['channel_name'] ?? 'CIOMAG TV')"
                            />
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Message d'accompagnement (Optionnel)</label>
                    <textarea
                        name="custom_message"
                        rows="3"
                        placeholder="Laissez vide pour utiliser le modèle automatique ({title} - {excerpt} {url})..."
                        class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-xs focus:border-red-700 focus:outline-none"
                    ></textarea>
                </div>

                <button type="submit" class="w-full py-3 px-4 rounded-xl bg-red-700 hover:bg-red-800 text-white font-bold text-xs uppercase tracking-wider transition shadow-sm flex items-center justify-center gap-2 cursor-pointer">
                    <i data-lucide="share-2" class="w-4 h-4"></i>
                    <span>Diffuser maintenant via API</span>
                </button>
            </form>
        </div>

        {{-- Paramétrage des clés API (Facebook, LinkedIn, X/Twitter, YouTube) --}}
        <div class="lg:col-span-7 bg-white rounded-xl border border-slate-200 p-6 shadow-2xs space-y-5">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-3">
                <div>
                    <h2 class="text-base font-black text-slate-900 flex items-center gap-2">
                        <i data-lucide="key-round" class="w-4 h-4 text-red-700"></i>
                        <span>Paramétrage des Connecteurs API Réseaux Sociaux</span>
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Configurez vos tokens d'accès Graph API, LinkedIn UGC, X/Twitter v2 et YouTube Data API.</p>
                </div>
                <span class="px-2.5 py-1 rounded-full text-[11px] font-extrabold uppercase {{ ($config['mode'] ?? 'sandbox') === 'live' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                    Mode actif : {{ ($config['mode'] ?? 'sandbox') === 'live' ? 'Production Live HTTP' : 'Sandbox / Simulation' }}
                </span>
            </div>

            <form action="{{ route('admin.social.config.update') }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 rounded-xl bg-slate-50 border border-slate-200">
                    <x-ui.select
                        name="mode"
                        label="Mode d'exécution des appels API"
                        :value="$config['mode'] ?? 'sandbox'"
                        :options="[
                            ['value' => 'sandbox', 'label' => 'Sandbox (Simulation instantanée & journalisation)'],
                            ['value' => 'live', 'label' => 'Production Live (Appels HTTP réels vers les APIs)'],
                        ]"
                    />

                    <div class="flex items-end pb-1">
                        <x-ui.checkbox
                            name="auto_publish_on_post"
                            value="1"
                            :checked="(bool) ($config['auto_publish_on_post'] ?? false)"
                            label="Auto-diffusion à la publication"
                            description="Partager automatiquement tout nouvel article publié"
                        />
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Modèle de message par défaut</label>
                        <input
                            type="text"
                            name="default_template"
                            value="{{ $config['default_template'] ?? '📰 {title} — {excerpt} 👉 {url} #CIOMAG #Afrique #Tech' }}"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-mono"
                        >
                        <span class="text-[10px] text-slate-400">Variables supportées : <code>{title}</code>, <code>{excerpt}</code>, <code>{url}</code></span>
                    </div>
                </div>

                {{-- Cartes de configuration par réseau --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    {{-- Facebook Graph API --}}
                    <div class="p-4 rounded-xl border border-slate-200 space-y-2.5">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black uppercase tracking-wider text-blue-700 flex items-center gap-1.5">
                                <i data-lucide="facebook" class="w-4 h-4"></i> Facebook Graph API
                            </span>
                            <x-ui.checkbox name="fb_enabled" value="1" :checked="(bool) ($config['facebook']['enabled'] ?? true)" label="Actif" />
                        </div>
                        <input type="text" name="fb_page_name" value="{{ $config['facebook']['page_name'] ?? '' }}" placeholder="Nom de la Page (ex: CIOMAG Officiel)" class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs">
                        <div class="grid grid-cols-3 gap-2">
                            <input type="text" name="fb_page_id" value="{{ $config['facebook']['page_id'] ?? '' }}" placeholder="Page ID" class="col-span-2 rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs font-mono">
                            <input type="text" name="fb_api_version" value="{{ $config['facebook']['api_version'] ?? 'v21.0' }}" placeholder="v21.0" class="rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs font-mono">
                        </div>
                        <input type="password" name="fb_access_token" value="{{ $config['facebook']['access_token'] ?? '' }}" placeholder="Page Access Token (EAAG...)" class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs font-mono">
                    </div>

                    {{-- LinkedIn UGC API --}}
                    <div class="p-4 rounded-xl border border-slate-200 space-y-2.5">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black uppercase tracking-wider text-sky-700 flex items-center gap-1.5">
                                <i data-lucide="linkedin" class="w-4 h-4"></i> LinkedIn Marketing API
                            </span>
                            <x-ui.checkbox name="li_enabled" value="1" :checked="(bool) ($config['linkedin']['enabled'] ?? true)" label="Actif" />
                        </div>
                        <input type="text" name="li_organization_name" value="{{ $config['linkedin']['organization_name'] ?? '' }}" placeholder="Nom Organisation (ex: CIOMAG)" class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs">
                        <input type="text" name="li_organization_urn" value="{{ $config['linkedin']['organization_urn'] ?? '' }}" placeholder="urn:li:organization:123456" class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs font-mono">
                        <input type="password" name="li_access_token" value="{{ $config['linkedin']['access_token'] ?? '' }}" placeholder="OAuth2 Access Token (AQV...)" class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs font-mono">
                    </div>

                    {{-- X / Twitter API v2 --}}
                    <div class="p-4 rounded-xl border border-slate-200 space-y-2.5">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black uppercase tracking-wider text-slate-900 flex items-center gap-1.5">
                                <i data-lucide="twitter" class="w-4 h-4"></i> X / Twitter API v2
                            </span>
                            <x-ui.checkbox name="tw_enabled" value="1" :checked="(bool) ($config['twitter']['enabled'] ?? true)" label="Actif" />
                        </div>
                        <input type="text" name="tw_handle" value="{{ $config['twitter']['handle'] ?? '' }}" placeholder="Compte @CIOMAG_Afrique" class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs">
                        <div class="grid grid-cols-2 gap-2">
                            <input type="text" name="tw_api_key" value="{{ $config['twitter']['api_key'] ?? '' }}" placeholder="API Key" class="rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs font-mono">
                            <input type="password" name="tw_api_secret" value="{{ $config['twitter']['api_secret'] ?? '' }}" placeholder="API Secret" class="rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs font-mono">
                        </div>
                        <input type="password" name="tw_bearer_token" value="{{ $config['twitter']['bearer_token'] ?? '' }}" placeholder="OAuth2 User/Bearer Token" class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs font-mono">
                    </div>

                    {{-- YouTube Data API v3 (Prêt pour la publication vidéo) --}}
                    <div class="p-4 rounded-xl border border-slate-200 space-y-2.5">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black uppercase tracking-wider text-red-700 flex items-center gap-1.5">
                                <i data-lucide="youtube" class="w-4 h-4"></i> YouTube Data API v3
                            </span>
                            <x-ui.checkbox name="yt_enabled" value="1" :checked="(bool) ($config['youtube']['enabled'] ?? true)" label="Actif" />
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <input type="text" name="yt_channel_name" value="{{ $config['youtube']['channel_name'] ?? '' }}" placeholder="CIOMAG TV" class="rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs">
                            <input type="text" name="yt_channel_id" value="{{ $config['youtube']['channel_id'] ?? '' }}" placeholder="Channel ID (UC...)" class="rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs font-mono">
                        </div>
                        <input type="password" name="yt_api_key" value="{{ $config['youtube']['api_key'] ?? '' }}" placeholder="YouTube Data API Key" class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs font-mono">
                        <div class="grid grid-cols-2 gap-2">
                            <input type="text" name="yt_client_id" value="{{ $config['youtube']['client_id'] ?? '' }}" placeholder="OAuth Client ID" class="rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs font-mono">
                            <input type="password" name="yt_client_secret" value="{{ $config['youtube']['client_secret'] ?? '' }}" placeholder="Client Secret" class="rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs font-mono">
                        </div>
                    </div>

                </div>

                <div class="flex justify-end">
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-red-700 text-white font-bold text-xs uppercase tracking-wider transition flex items-center gap-2 cursor-pointer">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        <span>Enregistrer la configuration API</span>
                    </button>
                </div>
            </form>
        </div>

    </div>

    {{-- Journal historique des publications API --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="history" class="w-4 h-4 text-red-700"></i>
                    <span>Historique des Diffusions Réseaux Sociaux</span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Traçabilité complète des partages d'articles et de podcasts via Facebook, LinkedIn, X/Twitter et YouTube.</p>
            </div>

            <div class="flex flex-wrap items-center gap-1.5">
                <a href="{{ route('admin.social.index') }}" class="px-3 py-1.5 rounded-lg text-xs font-bold {{ !$platform ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">Tous</a>
                @foreach(['facebook' => 'Facebook', 'linkedin' => 'LinkedIn', 'twitter' => 'X / Twitter', 'youtube' => 'YouTube'] as $pKey => $pLabel)
                    <a href="{{ route('admin.social.index', ['platform' => $pKey]) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold {{ $platform === $pKey ? 'bg-red-700 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        {{ $pLabel }}
                    </a>
                @endforeach
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="py-3 px-4">Plateforme</th>
                        <th class="py-3 px-4">Contenu partagé</th>
                        <th class="py-3 px-4">Message diffusé</th>
                        <th class="py-3 px-4">ID Externe / Lien</th>
                        <th class="py-3 px-4">Statut API</th>
                        <th class="py-3 px-4">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($shares as $share)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 text-slate-800 text-xs font-bold">
                                    <i data-lucide="{{ $share->platformIcon() }}" class="w-3.5 h-3.5 text-red-700"></i>
                                    <span>{{ $share->platformLabel() }}</span>
                                </span>
                            </td>
                            <td class="py-3 px-4 text-xs">
                                @if($share->post)
                                    <div class="font-bold text-slate-900 line-clamp-1">{{ $share->post->title }}</div>
                                    <span class="text-[11px] text-slate-400">Article</span>
                                @elseif($share->podcast)
                                    <div class="font-bold text-slate-900 line-clamp-1">{{ $share->podcast->title }}</div>
                                    <span class="text-[11px] text-red-700 font-semibold">Podcast</span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-xs text-slate-600 max-w-md">
                                <div class="line-clamp-2">{{ $share->message }}</div>
                            </td>
                            <td class="py-3 px-4 text-xs font-mono">
                                @if($share->external_url)
                                    <a href="{{ $share->external_url }}" target="_blank" class="text-red-700 hover:underline inline-flex items-center gap-1">
                                        <span>{{ \Illuminate\Support\Str::limit($share->external_post_id ?: 'Voir post', 20) }}</span>
                                        <i data-lucide="external-link" class="w-3 h-3"></i>
                                    </a>
                                @else
                                    <span class="text-slate-400">{{ $share->external_post_id ?: '—' }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                @if($share->status === 'published')
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Diffusé (Live)</span>
                                @elseif($share->status === 'simulated')
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-sky-50 text-sky-700 border border-sky-200">Diffusé (Sandbox)</span>
                                @elseif($share->status === 'failed')
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200" title="{{ $share->error_message }}">Échec</span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700">{{ ucfirst($share->status) }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-xs text-slate-500">
                                {{ $share->shared_at?->diffForHumans() ?? $share->created_at?->diffForHumans() }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">Aucune publication sociale enregistrée.</td>
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
