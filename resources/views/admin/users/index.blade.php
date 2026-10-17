@extends('layouts.admin')

@section('title', 'Gestion des Utilisateurs & Équipe')
@section('header_title', 'Gestion des Utilisateurs')
@section('subtitle', 'Comptes d\'accès, gestion des rôles éditoriaux, activation et administration des membres')

@section('top_actions')
    <button type="button"
            onclick="window.dispatchEvent(new CustomEvent('open-user-modal', { detail: { mode: 'create' } }))"
            class="px-4 py-2 bg-red-700 hover:bg-red-800 text-white text-xs font-bold uppercase tracking-wider rounded-xl transition inline-flex items-center gap-2 shadow-xs cursor-pointer">
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
    class="space-y-8"
>

    <!-- KPIs Utilisateurs -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-2xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Utilisateurs</span>
                <div class="text-2xl font-black text-slate-900 mt-1">{{ $stats['total'] }}</div>
                <span class="text-[11px] text-slate-500">Tous les comptes enregistrés</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center">
                <i data-lucide="users" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-2xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Comptes Actifs</span>
                <div class="text-2xl font-black text-emerald-600 mt-1">{{ $stats['active'] }}</div>
                <span class="text-[11px] text-emerald-600 font-semibold">Accès opérationnel</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <i data-lucide="user-check" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-2xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Suspendus / Attente</span>
                <div class="text-2xl font-black text-amber-600 mt-1">{{ $stats['suspended'] + $stats['pending'] }}</div>
                <span class="text-[11px] text-amber-600 font-semibold">{{ $stats['suspended'] }} suspendu(s)</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <i data-lucide="user-x" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-2xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Administrateurs</span>
                <div class="text-2xl font-black text-red-700 mt-1">{{ $stats['admins'] }}</div>
                <span class="text-[11px] text-slate-500">Droits d'administration</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-red-50 text-red-700 flex items-center justify-center">
                <i data-lucide="shield-check" class="w-5 h-5"></i>
            </div>
        </div>
    </div>

    <!-- Barre de Recherche, Filtres & Bouton Créer -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 flex flex-col lg:flex-row lg:items-center justify-between gap-4 shadow-2xs">
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap items-center gap-2.5 flex-1">
            <div class="relative w-64">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                <input type="text" name="q" value="{{ $search ?? '' }}" placeholder="Nom, email, @identifiant..."
                       class="w-full pl-9 pr-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:outline-none focus:border-slate-900">
            </div>

            <div class="w-48">
                <x-ui.select name="role" :options="$roleFilterOptions" :value="$roleFilter ?? ''" :searchable="false" />
            </div>

            <div class="w-44">
                <x-ui.select name="status" :options="$statusFilterOptions" :value="$statusFilter ?? ''" :searchable="false" />
            </div>

            <button type="submit" class="px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl transition cursor-pointer inline-flex items-center gap-1.5">
                <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                <span>Filtrer</span>
            </button>

            @if($search || $roleFilter || $statusFilter)
                <a href="{{ route('admin.users.index') }}" class="px-3 py-2 text-xs font-semibold text-slate-500 hover:text-slate-800 transition">
                    Réinitialiser
                </a>
            @endif
        </form>

        <button type="button"
                @click="openCreate()"
                class="px-4 py-2.5 bg-red-700 hover:bg-red-800 text-white text-xs font-bold uppercase tracking-wider rounded-xl transition inline-flex items-center gap-2 shadow-xs cursor-pointer shrink-0">
            <i data-lucide="plus-circle" class="w-4 h-4"></i>
            <span>Nouvel Utilisateur</span>
        </button>
    </div>

    <!-- Tableau de Gestion des Utilisateurs -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-2xs">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-sm font-black uppercase tracking-wider text-slate-900 flex items-center gap-2">
                <i data-lucide="users" class="w-4 h-4 text-red-700"></i>
                <span>Comptes Utilisateurs ({{ $users->total() }})</span>
            </h2>
            <span class="text-xs text-slate-400">Page {{ $users->currentPage() }} sur {{ $users->lastPage() }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-500">
                        <th class="py-3.5 px-5">Utilisateur</th>
                        <th class="py-3.5 px-4">Rôle</th>
                        <th class="py-3.5 px-4">Statut</th>
                        <th class="py-3.5 px-4">Dernière Connexion</th>
                        <th class="py-3.5 px-4">Créé le</th>
                        <th class="py-3.5 px-5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-4 px-5">
                                <div class="flex items-center gap-3">
                                    @if($user->avatar_url)
                                        <img src="{{ $user->avatar_url }}" alt="{{ $user->display_name }}"
                                             class="w-10 h-10 rounded-xl object-cover border border-slate-200 shrink-0">
                                    @else
                                        <div class="w-10 h-10 rounded-xl bg-slate-900 text-white font-extrabold text-xs flex items-center justify-center shrink-0">
                                            {{ $user->initials() }}
                                        </div>
                                    @endif

                                    <div class="min-w-0">
                                        <div class="font-bold text-slate-900 text-xs flex items-center gap-1.5 truncate">
                                            <span>{{ $user->display_name }}</span>
                                            @if($user->id === auth()->id())
                                                <span class="px-1.5 py-0.2 text-[9px] font-extrabold uppercase bg-red-100 text-red-700 rounded-sm">Vous</span>
                                            @endif
                                        </div>
                                        <div class="text-[11px] text-slate-500 truncate">{{ $user->email }}</div>
                                        <div class="text-[10px] font-mono text-slate-400 truncate">@<span>{{ $user->username }}</span></div>
                                    </div>
                                </div>
                            </td>

                            <td class="py-4 px-4 text-xs">
                                @php
                                    $roleBadgeColor = match($user->role?->slug) {
                                        'admin' => 'bg-red-100 text-red-800 border-red-200',
                                        'editor' => 'bg-blue-100 text-blue-800 border-blue-200',
                                        'author' => 'bg-amber-100 text-amber-800 border-amber-200',
                                        default => 'bg-slate-100 text-slate-700 border-slate-200',
                                    };
                                @endphp
                                <span class="px-2.5 py-1 text-[10px] font-bold uppercase rounded-md border {{ $roleBadgeColor }}">
                                    {{ $user->role?->name ?? 'Membre' }}
                                </span>
                            </td>

                            <td class="py-4 px-4">
                                @php
                                    $statusStyle = match($user->status->value) {
                                        'active' => 'bg-emerald-100 text-emerald-800',
                                        'suspended' => 'bg-rose-100 text-rose-800',
                                        'pending' => 'bg-amber-100 text-amber-800',
                                        default => 'bg-slate-100 text-slate-700',
                                    };
                                @endphp
                                <span class="px-2.5 py-1 text-[10px] font-bold uppercase rounded-md {{ $statusStyle }}">
                                    {{ $user->status->label() }}
                                </span>
                            </td>

                            <td class="py-4 px-4 text-xs text-slate-500">
                                @if($user->last_login_at)
                                    <div>{{ $user->last_login_at->format('d/m/Y') }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $user->last_login_at->format('H:i') }}</div>
                                @else
                                    <span class="text-slate-400 italic">Jamais connecté</span>
                                @endif
                            </td>

                            <td class="py-4 px-4 text-xs text-slate-500">
                                {{ $user->created_at->format('d/m/Y') }}
                            </td>

                            <td class="py-4 px-5 text-right">
                                <div class="inline-flex items-center justify-end gap-1.5">

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
                                                        class="p-2 text-amber-700 hover:bg-amber-50 rounded-lg transition cursor-pointer"
                                                        title="Désactiver / Suspendre">
                                                    <i data-lucide="pause-circle" class="w-4 h-4"></i>
                                                </button>
                                            @else
                                                <button type="submit"
                                                        class="p-2 text-emerald-700 hover:bg-emerald-50 rounded-lg transition cursor-pointer"
                                                        title="Activer le compte">
                                                    <i data-lucide="play-circle" class="w-4 h-4"></i>
                                                </button>
                                            @endif
                                        </form>
                                    @endif

                                    {{-- Bouton Modifier (Ouvre le même Dialog composant) --}}
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
                                            class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold rounded-lg inline-flex items-center gap-1.5 transition cursor-pointer">
                                        <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                        <span>Modifier</span>
                                    </button>

                                    {{-- Bouton Supprimer (avec dialog de confirmation réutilisable) --}}
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
                                                    class="p-2 text-rose-600 hover:bg-rose-50 rounded-lg transition cursor-pointer"
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

    <!-- ========================================================================= -->
    <!-- DIALOG COMPOSANT UNIQUE : AJOUT & MODIFICATION D'UN UTILISATEUR           -->
    <!-- ========================================================================= -->
    <div x-show="modalOpen"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs"
         @keydown.escape.window="modalOpen = false">
        <div @click.outside="modalOpen = false"
             class="bg-white rounded-2xl border border-slate-200 shadow-2xl w-full max-w-xl overflow-hidden relative">

            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                <h3 class="text-sm font-black uppercase tracking-wider text-slate-900 flex items-center gap-2">
                    <i data-lucide="user-check" class="w-4 h-4 text-red-700" x-show="isEditing"></i>
                    <i data-lucide="user-plus" class="w-4 h-4 text-red-700" x-show="!isEditing"></i>
                    <span x-text="modalTitle"></span>
                </h3>
                <button type="button" @click="modalOpen = false" class="p-1.5 text-slate-400 hover:text-slate-700 rounded-lg cursor-pointer">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form :action="formAction" method="POST" class="p-6 space-y-4 max-h-[82vh] overflow-y-auto">
                @csrf
                <template x-if="isEditing">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nom complet *</label>
                        <input type="text" name="display_name" required x-model="form.display_name" placeholder="Ex: Aïcha Diop"
                               class="w-full px-3.5 py-2 text-sm border border-slate-300 rounded-xl focus:outline-none focus:border-red-700">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Adresse email *</label>
                        <input type="email" name="email" required x-model="form.email" placeholder="aicha.diop@ciomag.com"
                               class="w-full px-3.5 py-2 text-sm border border-slate-300 rounded-xl focus:outline-none focus:border-red-700">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nom d'utilisateur (Identifiant)</label>
                        <input type="text" name="username" x-model="form.username" placeholder="aicha-diop"
                               class="w-full px-3.5 py-2 text-sm font-mono border border-slate-300 rounded-xl focus:outline-none focus:border-red-700">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            <span x-text="isEditing ? 'Nouveau mot de passe' : 'Mot de passe *'"></span>
                        </label>
                        <input type="password" name="password" :required="!isEditing" x-model="form.password"
                               :placeholder="isEditing ? 'Laisser vide pour ne pas changer' : '6 caractères minimum'"
                               class="w-full px-3.5 py-2 text-sm border border-slate-300 rounded-xl focus:outline-none focus:border-red-700">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Rôle & Permissions *</label>
                        <x-ui.select name="role_id" :options="$roleOptions" x-model="form.role_id" :searchable="false" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Statut du compte *</label>
                        <x-ui.select name="status" :options="$statusOptions" x-model="form.status" :searchable="false" />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">URL Avatar (Photo)</label>
                    <div class="flex gap-2">
                        <input type="text" name="avatar_url" x-model="form.avatar_url" placeholder="https://..."
                               class="w-full px-3.5 py-2 text-sm border border-slate-300 rounded-xl focus:outline-none focus:border-red-700">
                        <button type="button"
                                onclick="window.openMediaManager && window.openMediaManager((media) => {
                                    const modal = document.querySelector('[x-data]');
                                    if (modal && modal._x_dataStack) {
                                        modal._x_dataStack[0].form.avatar_url = media.url;
                                    }
                                })"
                                class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold shrink-0 transition cursor-pointer"
                                title="Choisir un média">
                            <i data-lucide="image" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Biographie d'auteur / Rédacteur</label>
                    <textarea name="bio" rows="2" x-model="form.bio" placeholder="Bio succincte affichée sous les articles..."
                              class="w-full px-3.5 py-2 text-sm border border-slate-300 rounded-xl focus:outline-none focus:border-red-700"></textarea>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                    <button type="button" @click="modalOpen = false" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl cursor-pointer">
                        Annuler
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-red-700 hover:bg-red-800 text-white text-xs font-bold uppercase tracking-wider rounded-xl inline-flex items-center gap-1.5 cursor-pointer shadow-xs">
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
