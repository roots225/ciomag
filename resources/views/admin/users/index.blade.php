@extends('layouts.admin')

@section('title', 'Gestion des Utilisateurs & Équipe')
@section('header_title', 'Gestion des Utilisateurs')
@section('subtitle', 'Comptes d\'accès, gestion des rôles éditoriaux, activation et administration des membres')

@section('top_actions')
    <button type="button"
            onclick="window.dispatchEvent(new CustomEvent('open-user-modal', { detail: { mode: 'create' } }))"
            class="btn btn-sm btn-primary">
        <i data-lucide="user-plus" class="w-4 h-4"></i>
        <span>Ajouter un utilisateur</span>
    </button>
@endsection

@section('content')
@php
    $roleOptions = $roles->mapWithKeys(fn ($r) => [
        (string) $r->id => $r->name . ' (' . $r->slug . ')',
    ])->all();

    $roleFilterOptions = ['' => 'Tous les rôles'] + $roles->mapWithKeys(fn ($r) => [(string) $r->id => $r->name])->all();

    $statusOptions = [
        'active' => 'Actif (Accès autorisé)',
        'suspended' => 'Suspendu (Accès bloqué)',
        'pending' => 'En attente de validation',
    ];

    $statusFilterOptions = [
        '' => 'Tous les statuts',
        'active' => 'Actifs',
        'suspended' => 'Suspendus',
        'pending' => 'En attente',
    ];

    $defaultRoleId = (string) ($roles->first()?->id ?? '1');
@endphp

<div x-data="{
        modalOpen: false,
        isEditing: false,
        modalTitle: 'Ajouter un utilisateur',
        formAction: '{{ route('admin.users.store') }}',
        form: {
            id: '',
            display_name: '',
            email: '',
            username: '',
            role_id: @js($defaultRoleId),
            status: 'active',
            password: '',
            bio: '',
            avatar_url: ''
        },

        openCreate() {
            this.isEditing = false;
            this.modalTitle = 'Ajouter un nouvel utilisateur';
            this.formAction = '{{ route('admin.users.store') }}';
            this.form = {
                id: '',
                display_name: '',
                email: '',
                username: '',
                role_id: @js($defaultRoleId),
                status: 'active',
                password: '',
                bio: '',
                avatar_url: ''
            };
            this.modalOpen = true;
            this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
        },

        openEdit(user) {
            this.isEditing = true;
            this.modalTitle = 'Modifier l\'utilisateur : ' + user.display_name;
            this.formAction = '/admin/users/' + user.id;
            this.form = {
                id: String(user.id),
                display_name: user.display_name || '',
                email: user.email || '',
                username: user.username || '',
                role_id: String(user.role_id || @js($defaultRoleId)),
                status: user.status || 'active',
                password: '',
                bio: user.bio || '',
                avatar_url: user.avatar_url || ''
            };
            this.modalOpen = true;
            this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
        }
    }"
    @open-user-modal.window="if ($event.detail.mode === 'create') openCreate(); else openEdit($event.detail.user);"
    class="space-y-6"
>

    <!-- KPIs Utilisateurs -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="admin-card p-4 flex items-center justify-between">
            <div>
                <span class="text-xs font-medium text-slate-500">Total Utilisateurs</span>
                <div class="text-xl font-bold text-slate-900 mt-0.5">{{ $stats['total'] }}</div>
                <span class="text-[11px] text-slate-400">Comptes enregistrés</span>
            </div>
            <div class="w-10 h-10 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center">
                <i data-lucide="users" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="admin-card p-4 flex items-center justify-between">
            <div>
                <span class="text-xs font-medium text-slate-500">Comptes Actifs</span>
                <div class="text-xl font-bold text-emerald-600 mt-0.5">{{ $stats['active'] }}</div>
                <span class="text-[11px] text-emerald-600 font-medium">Accès opérationnel</span>
            </div>
            <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <i data-lucide="user-check" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="admin-card p-4 flex items-center justify-between">
            <div>
                <span class="text-xs font-medium text-slate-500">Suspendus / Attente</span>
                <div class="text-xl font-bold text-amber-600 mt-0.5">{{ $stats['suspended'] + $stats['pending'] }}</div>
                <span class="text-[11px] text-amber-600 font-medium">{{ $stats['suspended'] }} suspendu(s)</span>
            </div>
            <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                <i data-lucide="user-x" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="admin-card p-4 flex items-center justify-between">
            <div>
                <span class="text-xs font-medium text-slate-500">Administrateurs</span>
                <div class="text-xl font-bold text-red-700 mt-0.5">{{ $stats['admins'] }}</div>
                <span class="text-[11px] text-slate-400">Droits système</span>
            </div>
            <div class="w-10 h-10 rounded-lg bg-red-50 text-red-700 flex items-center justify-center">
                <i data-lucide="shield-check" class="w-5 h-5"></i>
            </div>
        </div>
    </div>

    <!-- Barre de Recherche, Filtres & Bouton Créer -->
    <div class="admin-card p-4 flex flex-col lg:flex-row lg:items-center justify-between gap-3">
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap items-center gap-2 flex-1">
            <div class="relative w-60">
                <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                <input type="text" name="q" value="{{ $search ?? '' }}" placeholder="Nom, email, @identifiant..."
                       class="w-full pl-8 pr-3 py-1.5 text-xs border border-slate-200 rounded-lg focus:outline-none focus:border-slate-800">
            </div>

            <div class="w-44">
                <x-ui.select name="role" :options="$roleFilterOptions" :value="$roleFilter ?? ''" :searchable="false" />
            </div>

            <div class="w-40">
                <x-ui.select name="status" :options="$statusFilterOptions" :value="$statusFilter ?? ''" :searchable="false" />
            </div>

            <button type="submit" class="btn btn-sm btn-dark">
                <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                <span>Filtrer</span>
            </button>

            @if($search || $roleFilter || $statusFilter)
                <a href="{{ route('admin.users.index') }}" class="text-xs text-slate-500 hover:text-slate-800 px-2 py-1">
                    Réinitialiser
                </a>
            @endif
        </form>

        <button type="button"
                @click="openCreate()"
                class="btn btn-sm btn-primary self-start lg:self-auto shrink-0">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Nouvel Utilisateur</span>
        </button>
    </div>

    <!-- Tableau de Gestion des Utilisateurs -->
    <div class="admin-table-container">
        <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-slate-900 flex items-center gap-2">
                <i data-lucide="users" class="w-4 h-4 text-red-700"></i>
                <span>Comptes Utilisateurs ({{ $users->total() }})</span>
            </h2>
            <span class="text-xs text-slate-400">Page {{ $users->currentPage() }} sur {{ $users->lastPage() }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Utilisateur</th>
                        <th>Rôle</th>
                        <th>Statut</th>
                        <th>Dernière Connexion</th>
                        <th>Créé le</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td>
                                <div class="flex items-center gap-3">
                                    @if($user->avatar_url)
                                        <img src="{{ $user->avatar_url }}" alt="{{ $user->display_name }}"
                                             class="w-9 h-9 rounded-lg object-cover border border-slate-200 shrink-0">
                                    @else
                                        <div class="w-9 h-9 rounded-lg bg-slate-900 text-white font-bold text-xs flex items-center justify-center shrink-0">
                                            {{ $user->initials() }}
                                        </div>
                                    @endif

                                    <div class="min-w-0">
                                        <div class="font-semibold text-slate-900 text-xs flex items-center gap-1.5 truncate">
                                            <span>{{ $user->display_name }}</span>
                                            @if($user->id === auth()->id())
                                                <span class="admin-badge badge-primary text-[9px] py-0 px-1.5">Vous</span>
                                            @endif
                                        </div>
                                        <div class="text-[11px] text-slate-500 truncate">{{ $user->email }}</div>
                                        <div class="text-[10px] font-mono text-slate-400 truncate">@<span>{{ $user->username }}</span></div>
                                    </div>
                                </div>
                            </td>

                            <td>
                                @php
                                    $roleBadge = match($user->role?->slug) {
                                        'admin' => 'badge-danger',
                                        'editor' => 'badge-info',
                                        'author' => 'badge-warning',
                                        default => 'badge-neutral',
                                    };
                                @endphp
                                <span class="admin-badge {{ $roleBadge }}">
                                    {{ $user->role?->name ?? 'Membre' }}
                                </span>
                            </td>

                            <td>
                                @php
                                    $statusBadge = match($user->status->value) {
                                        'active' => 'badge-success',
                                        'suspended' => 'badge-danger',
                                        'pending' => 'badge-warning',
                                        default => 'badge-neutral',
                                    };
                                @endphp
                                <span class="admin-badge {{ $statusBadge }}">
                                    {{ $user->status->label() }}
                                </span>
                            </td>

                            <td class="text-xs text-slate-500">
                                @if($user->last_login_at)
                                    <div class="font-medium text-slate-700">{{ $user->last_login_at->format('d/m/Y') }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $user->last_login_at->format('H:i') }}</div>
                                @else
                                    <span class="text-slate-400 italic">Jamais connecté</span>
                                @endif
                            </td>

                            <td class="text-xs text-slate-500">
                                {{ $user->created_at->format('d/m/Y') }}
                            </td>

                            <td class="text-right">
                                <div class="inline-flex items-center justify-end gap-1">

                                    {{-- Bouton Activer / Désactiver --}}
                                    @if($user->id !== auth()->id())
                                        <form id="toggle-user-form-{{ $user->id }}" action="{{ route('admin.users.toggle', $user) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            @if($user->status->value === 'active')
                                                <button type="button"
                                                        onclick="window.confirmAction({
                                                            title: 'Désactiver le compte',
                                                            message: 'Voulez-vous suspendre l\'accès de « {{ addslashes($user->display_name) }} » ? L\'utilisateur ne pourra plus se connecter.',
                                                            formId: 'toggle-user-form-{{ $user->id }}',
                                                            confirmText: 'Désactiver le compte',
                                                            variant: 'warning',
                                                            icon: 'user-x'
                                                        })"
                                                        class="p-1.5 text-amber-600 hover:bg-amber-50 rounded-lg transition cursor-pointer"
                                                        title="Désactiver / Suspendre">
                                                    <i data-lucide="pause-circle" class="w-4 h-4"></i>
                                                </button>
                                            @else
                                                <button type="submit"
                                                        class="p-1.5 text-emerald-600 hover:bg-emerald-50 rounded-lg transition cursor-pointer"
                                                        title="Activer le compte">
                                                    <i data-lucide="play-circle" class="w-4 h-4"></i>
                                                </button>
                                            @endif
                                        </form>
                                    @endif

                                    {{-- Bouton Modifier --}}
                                    @php
                                        $userData = [
                                            'id' => $user->id,
                                            'display_name' => $user->display_name,
                                            'email' => $user->email,
                                            'username' => $user->username,
                                            'role_id' => $user->role_id,
                                            'status' => $user->status->value,
                                            'bio' => $user->bio,
                                            'avatar_url' => $user->avatar_url,
                                        ];
                                    @endphp
                                    <button type="button"
                                            @click="openEdit(@js($userData))"
                                            class="btn btn-sm btn-secondary">
                                        <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                        <span>Modifier</span>
                                    </button>

                                    {{-- Bouton Supprimer --}}
                                    @if($user->id !== auth()->id())
                                        <form id="delete-user-form-{{ $user->id }}" action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button"
                                                    onclick="window.confirmAction({
                                                        title: 'Supprimer définitivement l\'utilisateur',
                                                        message: 'Êtes-vous sûr de vouloir supprimer définitivement le compte de « {{ addslashes($user->display_name) }} » ? Cette opération est irréversible.',
                                                        formId: 'delete-user-form-{{ $user->id }}',
                                                        confirmText: 'Supprimer le compte',
                                                        variant: 'danger',
                                                        icon: 'trash-2'
                                                    })"
                                                    class="p-1.5 text-rose-600 hover:bg-rose-50 rounded-lg transition cursor-pointer"
                                                    title="Supprimer">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-slate-400 text-xs">
                                Aucun utilisateur trouvé pour ces critères de recherche.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>
        {{ $users->links() }}
    </div>

    <!-- DIALOG COMPOSANT UNIQUE : AJOUT & MODIFICATION D'UN UTILISATEUR -->
    <div x-show="modalOpen"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/40 backdrop-blur-xs"
         @keydown.escape.window="modalOpen = false">
        <div @click.outside="modalOpen = false"
             class="bg-white rounded-xl border border-slate-200 shadow-xl w-full max-w-xl overflow-hidden relative">

            <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <h3 class="text-sm font-semibold text-slate-900 flex items-center gap-2">
                    <i data-lucide="user-check" class="w-4 h-4 text-red-700" x-show="isEditing"></i>
                    <i data-lucide="user-plus" class="w-4 h-4 text-red-700" x-show="!isEditing"></i>
                    <span x-text="modalTitle"></span>
                </h3>
                <button type="button" @click="modalOpen = false" class="p-1 text-slate-400 hover:text-slate-600 rounded cursor-pointer">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form :action="formAction" method="POST" class="p-5 space-y-4 max-h-[82vh] overflow-y-auto">
                @csrf
                <template x-if="isEditing">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="admin-label">Nom complet *</label>
                        <input type="text" name="display_name" required x-model="form.display_name" placeholder="Ex: Aïcha Diop"
                               class="admin-input">
                    </div>
                    <div>
                        <label class="admin-label">Adresse email *</label>
                        <input type="email" name="email" required x-model="form.email" placeholder="aicha.diop@ciomag.com"
                               class="admin-input">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="admin-label">Nom d'utilisateur (Identifiant)</label>
                        <input type="text" name="username" x-model="form.username" placeholder="aicha-diop"
                               class="admin-input font-mono">
                    </div>
                    <div>
                        <label class="admin-label">
                            <span x-text="isEditing ? 'Nouveau mot de passe' : 'Mot de passe *'"></span>
                        </label>
                        <input type="password" name="password" :required="!isEditing" x-model="form.password"
                               :placeholder="isEditing ? 'Laisser vide pour ne pas changer' : '6 caractères minimum'"
                               class="admin-input">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="admin-label">Rôle & Permissions *</label>
                        <x-ui.select name="role_id" :options="$roleOptions" x-model="form.role_id" :searchable="false" />
                    </div>
                    <div>
                        <label class="admin-label">Statut du compte *</label>
                        <x-ui.select name="status" :options="$statusOptions" x-model="form.status" :searchable="false" />
                    </div>
                </div>

                <div>
                    <label class="admin-label">URL Avatar (Photo)</label>
                    <div class="flex gap-2">
                        <input type="text" name="avatar_url" x-model="form.avatar_url" placeholder="https://..."
                               class="admin-input">
                        <button type="button"
                                onclick="window.openMediaManager && window.openMediaManager((media) => {
                                    const modal = document.querySelector('[x-data]');
                                    if (modal && modal._x_dataStack) {
                                        modal._x_dataStack[0].form.avatar_url = media.url;
                                    }
                                })"
                                class="btn btn-sm btn-secondary shrink-0"
                                title="Choisir un média">
                            <i data-lucide="image" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <div>
                    <label class="admin-label">Biographie d'auteur / Rédacteur</label>
                    <textarea name="bio" rows="2" x-model="form.bio" placeholder="Bio succincte affichée sous les articles..."
                              class="admin-input"></textarea>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="modalOpen = false" class="btn btn-sm btn-ghost">
                        Annuler
                    </button>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i data-lucide="save" class="w-4 h-4" x-show="isEditing"></i>
                        <i data-lucide="plus" class="w-4 h-4" x-show="!isEditing"></i>
                        <span x-text="isEditing ? 'Enregistrer les modifications' : 'Créer le compte utilisateur'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
