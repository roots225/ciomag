@extends('layouts.admin')

@section('title', 'Configuration, Redirections SEO & Équipe')
@section('subtitle', 'Paramètres JSON (settings), redirections 301/302 (redirects) et utilisateurs/rôles')

@section('content')
@php
    $general = $settings['site_general']->value ?? [];
    $seoDefaults = $settings['seo_defaults']->value ?? [];

    $statusCodeOptions = [
        '301' => '301 — Redirection Permanente',
        '302' => '302 — Redirection Temporaire',
        '307' => '307 — Temporary Redirect',
        '308' => '308 — Permanent Redirect',
    ];

    $roleOptions = [];
    foreach ($roles as $role) {
        $roleOptions[$role->id] = $role->name;
    }
@endphp

<div class="space-y-10">

    <!-- 1. Paramètres Généraux & SEO (Table settings JSON) -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-2xs">
        <h2 class="text-sm font-black uppercase tracking-wider text-slate-900 border-b border-slate-100 pb-3 mb-5 flex items-center gap-2">
            <i data-lucide="sliders" class="w-4 h-4 text-red-700"></i>
            <span>1. Paramètres Généraux du Magazine (`settings` JSON)</span>
        </h2>

        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nom du Magazine *</label>
                    <input type="text" name="site_name" value="{{ $general['site_name'] ?? 'CIOMAG' }}" required class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Édition en cours</label>
                    <input type="text" name="edition" value="{{ $general['edition'] ?? '' }}" class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Email de la rédaction</label>
                    <input type="email" name="contact_email" value="{{ $general['contact_email'] ?? '' }}" class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Slogan / Tagline *</label>
                    <input type="text" name="tagline" value="{{ $general['tagline'] ?? '' }}" required class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Meta Title par défaut</label>
                    <input type="text" name="default_meta_title" value="{{ $seoDefaults['default_meta_title'] ?? '' }}" class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Texte « À propos » du pied de page</label>
                <textarea name="footer_about" rows="2" class="w-full px-3.5 py-2 text-sm border border-slate-300 rounded-xl">{{ $general['footer_about'] ?? '' }}</textarea>
            </div>

            <button type="submit" class="px-5 py-2.5 bg-red-700 hover:bg-red-800 text-white text-xs font-bold uppercase rounded-xl inline-flex items-center gap-1.5 cursor-pointer">
                <i data-lucide="save" class="w-4 h-4"></i>
                <span>Enregistrer la configuration</span>
            </button>
        </form>
    </div>

    <!-- 2. Redirections SEO 301/302 -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200 p-6 shadow-2xs">
            <h2 class="text-sm font-black uppercase tracking-wider text-slate-900 mb-4 flex items-center gap-2">
                <i data-lucide="corner-up-right" class="w-4 h-4 text-red-700"></i>
                <span>2. Ajouter une Redirection SEO</span>
            </h2>
            <form action="{{ route('admin.redirects.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Ancien chemin (`source_path`) *</label>
                    <input type="text" name="source_path" required placeholder="/ancien-slug" class="w-full px-3.5 py-2.5 text-sm font-mono border border-slate-300 rounded-xl">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nouvelle cible (`target_path`) *</label>
                    <input type="text" name="target_path" required placeholder="/blog/nouveau-slug" class="w-full px-3.5 py-2.5 text-sm font-mono border border-slate-300 rounded-xl">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Code HTTP (`status_code`)</label>
                    <x-ui.select
                        name="status_code"
                        :options="$statusCodeOptions"
                        value="301"
                        :searchable="false"
                    />
                </div>
                <button type="submit" class="px-5 py-2.5 bg-slate-900 hover:bg-red-700 text-white text-xs font-bold uppercase rounded-xl inline-flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Créer la redirection</span>
                </button>
            </form>
        </div>

        <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-2xs">
            <div class="px-6 py-4 border-b border-slate-100">
                <h2 class="text-sm font-black uppercase tracking-wider text-slate-900">Redirections Actives ({{ $redirects->count() }})</h2>
            </div>
            <div class="divide-y divide-slate-100 text-sm">
                @foreach($redirects as $r)
                    <div class="p-4 flex items-center justify-between gap-4">
                        <div class="font-mono text-xs flex items-center flex-wrap gap-1.5">
                            <span class="font-bold text-slate-900">{{ $r->source_path }}</span>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5 text-slate-400"></i>
                            <span class="text-emerald-700">{{ $r->target_path }}</span>
                            <span class="px-1.5 py-0.5 bg-slate-100 text-slate-600 rounded-xs">{{ $r->status_code }}</span>
                            <span class="text-slate-400">({{ $r->hits }} hits)</span>
                        </div>
                        <form action="{{ route('admin.redirects.destroy', $r) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-1.5 text-rose-600 hover:bg-rose-50 rounded-lg cursor-pointer" title="Supprimer">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- 3. Équipe Éditoriale & Rôles (Accès direct à la gestion dédiée) -->
    <div class="space-y-4">
        <div class="bg-slate-900 rounded-2xl p-6 text-white shadow-md flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-red-700 text-white flex items-center justify-center shrink-0">
                    <i data-lucide="users" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black uppercase tracking-wider text-white">Gestion Dédiée des Utilisateurs</h3>
                    <p class="text-xs text-slate-300 mt-0.5">Consultez, ajoutez, modifiez, activez ou désactivez les comptes et rôles de l'équipe dans un espace dédié avec dialog modal.</p>
                </div>
            </div>
            <a href="{{ route('admin.users.index') }}" class="px-5 py-2.5 bg-red-700 hover:bg-red-800 text-white text-xs font-bold uppercase tracking-wider rounded-xl transition inline-flex items-center gap-2 shrink-0">
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
                <span>Gérer les utilisateurs</span>
            </a>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-2xs">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <h2 class="text-sm font-black uppercase tracking-wider text-slate-900">Aperçu de l'Équipe ({{ $users->count() }})</h2>
                <a href="{{ route('admin.users.index') }}" class="text-xs font-bold text-red-700 hover:underline">Voir tous les utilisateurs →</a>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach($users->take(5) as $u)
                    <div class="p-4 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            @if($u->avatar_url)
                                <img src="{{ $u->avatar_url }}" alt="{{ $u->display_name }}" class="w-10 h-10 rounded-xl object-cover border border-slate-200">
                            @else
                                <div class="w-10 h-10 rounded-xl bg-slate-900 text-white flex items-center justify-center text-xs font-bold">
                                    {{ $u->initials() }}
                                </div>
                            @endif
                            <div>
                                <div class="font-bold text-sm text-slate-900">{{ $u->display_name }} <span class="text-xs font-mono text-slate-400">(@{{ $u->username }})</span></div>
                                <div class="text-xs text-slate-500">{{ $u->email }} • {{ $u->role?->name }}</div>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 text-xs font-bold uppercase bg-emerald-50 text-emerald-700 rounded-full">
                            {{ $u->status->label() }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

</div>
@endsection
