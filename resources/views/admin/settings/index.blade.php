@extends('layouts.admin')

@section('title', 'Configuration & Redirections SEO')
@section('subtitle', 'Paramètres JSON (settings), redirections 301/302 (redirects) et équipe éditoriale')

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

<div class="space-y-6">

    <!-- 1. Paramètres Généraux & SEO -->
    <div class="admin-card p-5 sm:p-6 space-y-4">
        <h2 class="text-sm font-semibold text-slate-900 border-b border-slate-100 pb-3 flex items-center gap-2">
            <i data-lucide="sliders" class="w-4 h-4 text-red-700"></i>
            <span>Paramètres Généraux du Magazine</span>
        </h2>

        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="admin-label">Nom du Magazine *</label>
                    <input type="text" name="site_name" value="{{ $general['site_name'] ?? 'CIOMAG' }}" required class="admin-input">
                </div>
                <div>
                    <label class="admin-label">Édition en cours</label>
                    <input type="text" name="edition" value="{{ $general['edition'] ?? '' }}" class="admin-input">
                </div>
                <div>
                    <label class="admin-label">Email de la rédaction</label>
                    <input type="email" name="contact_email" value="{{ $general['contact_email'] ?? '' }}" class="admin-input">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="admin-label">Slogan / Tagline *</label>
                    <input type="text" name="tagline" value="{{ $general['tagline'] ?? '' }}" required class="admin-input">
                </div>
                <div>
                    <label class="admin-label">Meta Title par défaut</label>
                    <input type="text" name="default_meta_title" value="{{ $seoDefaults['default_meta_title'] ?? '' }}" class="admin-input">
                </div>
            </div>

            <div>
                <label class="admin-label">Texte « À propos » du pied de page</label>
                <textarea name="footer_about" rows="2" class="admin-input">{{ $general['footer_about'] ?? '' }}</textarea>
            </div>

            <button type="submit" class="btn btn-sm btn-primary">
                <i data-lucide="save" class="w-4 h-4"></i>
                <span>Enregistrer la configuration</span>
            </button>
        </form>
    </div>

    <!-- 2. Redirections SEO 301/302 -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-5 admin-card p-5 sm:p-6 space-y-4">
            <h2 class="text-sm font-semibold text-slate-900 flex items-center gap-2 border-b border-slate-100 pb-3">
                <i data-lucide="corner-up-right" class="w-4 h-4 text-red-700"></i>
                <span>Ajouter une Redirection SEO</span>
            </h2>
            <form action="{{ route('admin.redirects.store') }}" method="POST" class="space-y-3.5">
                @csrf
                <div>
                    <label class="admin-label">Ancien chemin (source_path) *</label>
                    <input type="text" name="source_path" required placeholder="/ancien-slug" class="admin-input font-mono text-xs">
                </div>
                <div>
                    <label class="admin-label">Nouvelle cible (target_path) *</label>
                    <input type="text" name="target_path" required placeholder="/blog/nouveau-slug" class="admin-input font-mono text-xs">
                </div>
                <div>
                    <label class="admin-label">Code HTTP (status_code)</label>
                    <x-ui.select
                        name="status_code"
                        :options="$statusCodeOptions"
                        value="301"
                        :searchable="false"
                    />
                </div>
                <button type="submit" class="btn btn-sm btn-dark">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Créer la redirection</span>
                </button>
            </form>
        </div>

        <div class="lg:col-span-7 admin-card overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100">
                <h2 class="text-sm font-semibold text-slate-900">Redirections Actives ({{ $redirects->count() }})</h2>
            </div>
            <div class="divide-y divide-slate-100 text-xs">
                @forelse($redirects as $r)
                    <div class="p-3.5 flex items-center justify-between gap-4 hover:bg-slate-50/70 transition-colors">
                        <div class="font-mono text-xs flex items-center flex-wrap gap-1.5">
                            <span class="font-semibold text-slate-900">{{ $r->source_path }}</span>
                            <i data-lucide="arrow-right" class="w-3 h-3 text-slate-400"></i>
                            <span class="text-emerald-700 font-medium">{{ $r->target_path }}</span>
                            <span class="admin-badge badge-neutral text-[10px]">{{ $r->status_code }}</span>
                            <span class="text-slate-400">({{ $r->hits }} hits)</span>
                        </div>
                        <form action="{{ route('admin.redirects.destroy', $r) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-1 text-rose-600 hover:bg-rose-50 rounded cursor-pointer" title="Supprimer">
                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                            </button>
                        </form>
                    </div>
                @empty
                    <div class="p-8 text-center text-xs text-slate-400">Aucune redirection active.</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- 3. Équipe Éditoriale & Rôles -->
    <div class="space-y-4">
        <div class="admin-card p-5 sm:p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-lg bg-red-50 text-red-700 flex items-center justify-center shrink-0">
                    <i data-lucide="users" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-slate-900">Gestion Dédiée des Utilisateurs</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Comptes, rôles éditoriaux, activation/désactivation et permissions.</p>
                </div>
            </div>
            <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-primary shrink-0 self-start sm:self-auto">
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                <span>Gérer les utilisateurs</span>
            </a>
        </div>

        <div class="admin-card overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-slate-900">Aperçu des Membres ({{ $users->count() }})</h2>
                <a href="{{ route('admin.users.index') }}" class="text-xs font-medium text-red-700 hover:underline">Voir tous les utilisateurs →</a>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach($users->take(5) as $u)
                    <div class="p-3.5 flex items-center justify-between gap-4 hover:bg-slate-50/70 transition-colors">
                        <div class="flex items-center gap-3">
                            @if($u->avatar_url)
                                <img src="{{ $u->avatar_url }}" alt="{{ $u->display_name }}" class="w-8 h-8 rounded-lg object-cover border border-slate-200">
                            @else
                                <div class="w-8 h-8 rounded-lg bg-slate-900 text-white flex items-center justify-center text-xs font-bold">
                                    {{ $u->initials() }}
                                </div>
                            @endif
                            <div>
                                <div class="font-semibold text-xs text-slate-900">{{ $u->display_name }} <span class="font-mono text-slate-400 font-normal">(@{{ $u->username }})</span></div>
                                <div class="text-[11px] text-slate-500">{{ $u->email }} • {{ $u->role?->name }}</div>
                            </div>
                        </div>
                        <span class="admin-badge badge-success text-[10px]">
                            {{ $u->status->label() }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

</div>
@endsection
