@extends('layouts.admin')

@section('title', 'Gestion de Mon Profil')
@section('header_title', 'Mon Profil')
@section('subtitle', 'Gérez vos informations personnelles, votre présentation d\'auteur et la sécurité de votre compte')

@section('top_actions')
    <a href="{{ route('admin.users.index') }}" class="px-3.5 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 text-xs font-bold uppercase tracking-wider rounded-xl inline-flex items-center gap-1.5 shadow-2xs">
        <i data-lucide="users" class="w-4 h-4 text-slate-500"></i>
        <span>Gestion des Utilisateurs</span>
    </a>
@endsection

@section('content')
<div class="max-w-4xl mx-auto space-y-8">

    <!-- Carte d'en-tête du profil -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-2xs flex flex-col sm:flex-row items-center sm:items-start gap-6">
        <div class="relative shrink-0">
            @if($user->avatar_url)
                <img src="{{ $user->avatar_url }}" alt="{{ $user->display_name }}"
                     class="w-24 h-24 rounded-2xl object-cover border-2 border-slate-200 shadow-sm">
            @else
                <div class="w-24 h-24 rounded-2xl bg-linear-to-tr from-slate-900 to-red-700 text-white font-black text-2xl flex items-center justify-center shadow-sm">
                    {{ $user->initials() }}
                </div>
            @endif
            <button type="button"
                    onclick="window.openMediaManager && window.openMediaManager((media) => {
                        const input = document.getElementById('profile_avatar_url');
                        if (input) { input.value = media.url; input.dispatchEvent(new Event('input')); }
                    })"
                    class="absolute -bottom-2 -right-2 p-2 bg-white hover:bg-slate-50 text-slate-800 border border-slate-200 rounded-xl shadow-xs cursor-pointer transition"
                    title="Changer la photo de profil">
                <i data-lucide="camera" class="w-4 h-4 text-red-700"></i>
            </button>
        </div>

        <div class="text-center sm:text-left flex-1 space-y-1">
            <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                <h2 class="text-xl font-black text-slate-900">{{ $user->display_name }}</h2>
                <span class="px-2.5 py-0.5 text-xs font-bold uppercase rounded-md bg-red-100 text-red-700">
                    {{ $user->role?->name ?? 'Membre' }}
                </span>
                <span class="px-2.5 py-0.5 text-xs font-bold uppercase rounded-md bg-emerald-100 text-emerald-800">
                    {{ $user->status->label() }}
                </span>
            </div>
            <p class="text-xs font-mono text-slate-500">{{ $user->email }} · @<span>{{ $user->username }}</span></p>

            <div class="pt-3 flex flex-wrap items-center justify-center sm:justify-start gap-4 text-xs text-slate-500">
                <span class="flex items-center gap-1.5">
                    <i data-lucide="shield" class="w-3.5 h-3.5 text-slate-400"></i>
                    <span>Rôle : <strong>{{ $user->role?->slug }}</strong></span>
                </span>
                <span class="flex items-center gap-1.5">
                    <i data-lucide="clock" class="w-3.5 h-3.5 text-slate-400"></i>
                    <span>Dernière connexion : <strong>{{ $user->last_login_at ? $user->last_login_at->format('d/m/Y à H:i') : 'Première session' }}</strong></span>
                </span>
                <span class="flex items-center gap-1.5">
                    <i data-lucide="calendar" class="w-3.5 h-3.5 text-slate-400"></i>
                    <span>Inscrit le : <strong>{{ $user->created_at->format('d/m/Y') }}</strong></span>
                </span>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.profile.update') }}" method="POST" class="space-y-8">
        @csrf
        @method('PUT')

        <!-- Section 1 : Informations personnelles -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-2xs space-y-6">
            <div class="border-b border-slate-100 pb-4">
                <h3 class="text-sm font-black uppercase tracking-wider text-slate-900 flex items-center gap-2">
                    <i data-lucide="user" class="w-4 h-4 text-red-700"></i>
                    <span>Informations Personnelles</span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Ces informations apparaissent sur vos publications et dans l'équipe éditoriale.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nom complet affiché *</label>
                    <input type="text" name="display_name" required value="{{ old('display_name', $user->display_name) }}"
                           class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:outline-none focus:border-red-700">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Adresse email *</label>
                    <input type="email" name="email" required value="{{ old('email', $user->email) }}"
                           class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:outline-none focus:border-red-700">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nom d'utilisateur (Identifiant URL)</label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs font-mono">@</span>
                        <input type="text" name="username" value="{{ old('username', $user->username) }}"
                               class="w-full pl-8 pr-3.5 py-2.5 text-sm font-mono border border-slate-300 rounded-xl focus:outline-none focus:border-red-700">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">URL de l'avatar</label>
                    <div class="flex gap-2">
                        <input type="text" id="profile_avatar_url" name="avatar_url" value="{{ old('avatar_url', $user->avatar_url) }}"
                               placeholder="https://..."
                               class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:outline-none focus:border-red-700">
                        <button type="button"
                                onclick="window.openMediaManager && window.openMediaManager((media) => {
                                    document.getElementById('profile_avatar_url').value = media.url;
                                })"
                                class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold shrink-0 transition cursor-pointer"
                                title="Choisir dans la médiathèque">
                            <i data-lucide="image" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Biographie d'auteur</label>
                <textarea name="bio" rows="3" placeholder="Présentation courte pour la signature de vos articles..."
                          class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:outline-none focus:border-red-700">{{ old('bio', $user->bio) }}</textarea>
            </div>
        </div>

        <!-- Section 2 : Sécurité & Changement de mot de passe -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-2xs space-y-6">
            <div class="border-b border-slate-100 pb-4">
                <h3 class="text-sm font-black uppercase tracking-wider text-slate-900 flex items-center gap-2">
                    <i data-lucide="key-round" class="w-4 h-4 text-amber-500"></i>
                    <span>Sécurité & Mot de Passe</span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Laissez ces champs vides si vous ne souhaitez pas modifier votre mot de passe.</p>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Mot de passe actuel</label>
                    <input type="password" name="current_password" placeholder="Requis uniquement en cas de changement"
                           class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:outline-none focus:border-red-700">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nouveau mot de passe</label>
                        <input type="password" name="new_password" placeholder="6 caractères minimum"
                               class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:outline-none focus:border-red-700">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Confirmer le nouveau mot de passe</label>
                        <input type="password" name="new_password_confirmation" placeholder="Retapez le nouveau mot de passe"
                               class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:outline-none focus:border-red-700">
                    </div>
                </div>
            </div>
        </div>

        <!-- Boutons de soumission -->
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.dashboard') }}" class="px-5 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-200 rounded-xl transition">
                Annuler
            </a>
            <button type="submit" class="px-6 py-3 bg-red-700 hover:bg-red-800 text-white text-xs font-bold uppercase tracking-wider rounded-xl shadow-xs inline-flex items-center gap-2 transition cursor-pointer">
                <i data-lucide="save" class="w-4 h-4"></i>
                <span>Enregistrer mon profil</span>
            </button>
        </div>
    </form>

</div>
@endsection
