@extends('layouts.admin')

@section('title', 'Gestion de Mon Profil')
@section('header_title', 'Mon Profil')
@section('subtitle', 'Gérez vos informations personnelles, votre présentation d\'auteur et la sécurité de votre compte')

@section('top_actions')
    <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-secondary">
        <i data-lucide="users" class="w-4 h-4 text-slate-500"></i>
        <span>Gestion des Utilisateurs</span>
    </a>
@endsection

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Carte d'en-tête du profil -->
    <div class="admin-card p-5 sm:p-6 flex flex-col sm:flex-row items-center sm:items-start gap-5">
        <div class="relative shrink-0">
            @if($user->avatar_url)
                <img src="{{ $user->avatar_url }}" alt="{{ $user->display_name }}"
                     class="w-20 h-20 rounded-xl object-cover border border-slate-200">
            @else
                <div class="w-20 h-20 rounded-xl bg-slate-900 text-white font-bold text-xl flex items-center justify-center">
                    {{ $user->initials() }}
                </div>
            @endif
            <button type="button"
                    onclick="window.openMediaManager && window.openMediaManager((media) => {
                        const input = document.getElementById('profile_avatar_url');
                        if (input) { input.value = media.url; input.dispatchEvent(new Event('input')); }
                    })"
                    class="absolute -bottom-1.5 -right-1.5 p-1.5 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 rounded-lg shadow-2xs cursor-pointer transition"
                    title="Changer la photo de profil">
                <i data-lucide="camera" class="w-3.5 h-3.5 text-red-700"></i>
            </button>
        </div>

        <div class="text-center sm:text-left flex-1 space-y-1">
            <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                <h2 class="text-lg font-bold text-slate-900">{{ $user->display_name }}</h2>
                <span class="admin-badge badge-primary">
                    {{ $user->role?->name ?? 'Membre' }}
                </span>
                <span class="admin-badge badge-success">
                    {{ $user->status->label() }}
                </span>
            </div>
            <p class="text-xs font-mono text-slate-500">{{ $user->email }} · @<span>{{ $user->username }}</span></p>

            <div class="pt-2 flex flex-wrap items-center justify-center sm:justify-start gap-4 text-xs text-slate-500">
                <span class="flex items-center gap-1.5">
                    <i data-lucide="shield" class="w-3.5 h-3.5 text-slate-400"></i>
                    <span>Rôle : <strong class="text-slate-700">{{ $user->role?->slug }}</strong></span>
                </span>
                <span class="flex items-center gap-1.5">
                    <i data-lucide="clock" class="w-3.5 h-3.5 text-slate-400"></i>
                    <span>Dernière connexion : <strong class="text-slate-700">{{ $user->last_login_at ? $user->last_login_at->format('d/m/Y à H:i') : 'Première session' }}</strong></span>
                </span>
                <span class="flex items-center gap-1.5">
                    <i data-lucide="calendar" class="w-3.5 h-3.5 text-slate-400"></i>
                    <span>Inscrit le : <strong class="text-slate-700">{{ $user->created_at->format('d/m/Y') }}</strong></span>
                </span>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.profile.update') }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Section 1 : Informations personnelles -->
        <div class="admin-card p-5 sm:p-6 space-y-4">
            <div class="border-b border-slate-100 pb-3">
                <h3 class="text-sm font-semibold text-slate-900 flex items-center gap-2">
                    <i data-lucide="user" class="w-4 h-4 text-red-700"></i>
                    <span>Informations Personnelles</span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Ces informations apparaissent sur vos publications et dans l'équipe éditoriale.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="admin-label">Nom complet affiché *</label>
                    <input type="text" name="display_name" required value="{{ old('display_name', $user->display_name) }}"
                           class="admin-input">
                </div>

                <div>
                    <label class="admin-label">Adresse email *</label>
                    <input type="email" name="email" required value="{{ old('email', $user->email) }}"
                           class="admin-input">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="admin-label">Nom d'utilisateur (Identifiant URL)</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs font-mono">@</span>
                        <input type="text" name="username" value="{{ old('username', $user->username) }}"
                               class="admin-input pl-7 font-mono">
                    </div>
                </div>

                <div>
                    <label class="admin-label">URL de l'avatar</label>
                    <div class="flex gap-2">
                        <input type="text" id="profile_avatar_url" name="avatar_url" value="{{ old('avatar_url', $user->avatar_url) }}"
                               placeholder="https://..."
                               class="admin-input">
                        <button type="button"
                                onclick="window.openMediaManager && window.openMediaManager((media) => {
                                    document.getElementById('profile_avatar_url').value = media.url;
                                })"
                                class="btn btn-sm btn-secondary shrink-0"
                                title="Choisir dans la médiathèque">
                            <i data-lucide="image" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div>
                <label class="admin-label">Biographie d'auteur</label>
                <textarea name="bio" rows="3" placeholder="Présentation courte pour la signature de vos articles..."
                          class="admin-input">{{ old('bio', $user->bio) }}</textarea>
            </div>
        </div>

        <!-- Section 2 : Sécurité & Changement de mot de passe -->
        <div class="admin-card p-5 sm:p-6 space-y-4">
            <div class="border-b border-slate-100 pb-3">
                <h3 class="text-sm font-semibold text-slate-900 flex items-center gap-2">
                    <i data-lucide="key-round" class="w-4 h-4 text-amber-500"></i>
                    <span>Sécurité & Mot de Passe</span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Laissez ces champs vides si vous ne souhaitez pas modifier votre mot de passe.</p>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="admin-label">Mot de passe actuel</label>
                    <input type="password" name="current_password" placeholder="Requis uniquement en cas de changement"
                           class="admin-input">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="admin-label">Nouveau mot de passe</label>
                        <input type="password" name="new_password" placeholder="6 caractères minimum"
                               class="admin-input">
                    </div>
                    <div>
                        <label class="admin-label">Confirmer le nouveau mot de passe</label>
                        <input type="password" name="new_password_confirmation" placeholder="Retapez le nouveau mot de passe"
                               class="admin-input">
                    </div>
                </div>
            </div>
        </div>

        <!-- Boutons de soumission -->
        <div class="flex items-center justify-end gap-2.5">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-md btn-ghost">
                Annuler
            </a>
            <button type="submit" class="btn btn-md btn-primary">
                <i data-lucide="save" class="w-4 h-4"></i>
                <span>Enregistrer mon profil</span>
            </button>
        </div>
    </form>

</div>
@endsection
