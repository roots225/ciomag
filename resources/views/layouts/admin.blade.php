<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Studio Rédaction') — CIOMAG Admin</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Cropper.js pour le composant universel de recadrage de médias -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Police Alan Sans pour l'administration CIOMAG -->
    <link rel="preload" href="/fonts/Alan_Sans/AlanSans-VariableFont_wght.ttf" as="font" type="font/ttf" crossorigin>

    @stack('head')
</head>

<body class="admin-body font-alan bg-slate-100 text-slate-900 antialiased h-screen overflow-hidden flex"
    style="font-family: 'Alan Sans', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">

    <!-- Sidebar 100vh fixe avec zone de navigation scrollable -->
    <aside
        class="w-64 bg-slate-950 text-slate-300 flex flex-col shrink-0 h-screen overflow-hidden border-r border-slate-800">
        <!-- En-tête fixe de la Sidebar -->
        <div class="p-5 border-b border-slate-800 flex items-center justify-between shrink-0">
            <a href="{{ route('admin.dashboard') }}" class="flex items-baseline gap-1">
                <span class="text-2xl font-black tracking-tighter text-white">CIO</span>
                <span class="text-2xl font-light tracking-tight text-red-500">MAG</span>
                <span
                    class="ml-1.5 px-1.5 py-0.5 text-[10px] font-bold uppercase bg-red-700 text-white rounded-xs">CMS</span>
            </a>
        </div>

        <!-- Contenu Scrollable de la Sidebar -->
        <nav class="flex-1 p-3 space-y-1 text-xs font-medium overflow-y-auto">
            <div class="px-3 pt-2 pb-1 text-[10px] font-semibold uppercase tracking-wider text-slate-500">
                Éditorial
            </div>

            <a href="{{ route('admin.dashboard') }}"
                class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.dashboard') ? 'bg-red-700 text-white font-semibold shadow-xs' : 'hover:bg-slate-900 hover:text-white' }}">
                <i data-lucide="layout-dashboard" class="w-4 h-4 shrink-0"></i>
                <span>Tableau de bord</span>
            </a>

            <a href="{{ route('admin.posts.index', ['type' => 'post']) }}"
                class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.posts.*') && request('type') !== 'page' ? 'bg-red-700 text-white font-semibold shadow-xs' : 'hover:bg-slate-900 hover:text-white' }}">
                <i data-lucide="newspaper" class="w-4 h-4 shrink-0"></i>
                <span>Articles</span>
            </a>

            <a href="{{ route('admin.posts.index', ['type' => 'page']) }}"
                class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.posts.*') && request('type') === 'page' ? 'bg-red-700 text-white font-semibold shadow-xs' : 'hover:bg-slate-900 hover:text-white' }}">
                <i data-lucide="layout-template" class="w-4 h-4 shrink-0"></i>
                <span>Pages</span>
            </a>

            <div class="px-3 pt-3 pb-1 text-[10px] font-semibold uppercase tracking-wider text-slate-500">
                Taxonomie & Ressources
            </div>

            <a href="{{ route('admin.categories.index') }}"
                class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.categories.*') ? 'bg-red-700 text-white font-semibold shadow-xs' : 'hover:bg-slate-900 hover:text-white' }}">
                <i data-lucide="folder-tree" class="w-4 h-4 shrink-0"></i>
                <span>Catégories & Tags</span>
            </a>

            <a href="{{ route('admin.media.index') }}"
                class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.media.*') ? 'bg-red-700 text-white font-semibold shadow-xs' : 'hover:bg-slate-900 hover:text-white' }}">
                <i data-lucide="image" class="w-4 h-4 shrink-0"></i>
                <span>Médiathèque</span>
            </a>

            <a href="{{ route('admin.comments.index') }}"
                class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.comments.*') ? 'bg-red-700 text-white font-semibold shadow-xs' : 'hover:bg-slate-900 hover:text-white' }}">
                <i data-lucide="message-square" class="w-4 h-4 shrink-0"></i>
                <span>Commentaires</span>
            </a>

            <div class="px-3 pt-3 pb-1 text-[10px] font-semibold uppercase tracking-wider text-slate-500">
                Monétisation & Médias
            </div>

            {{-- Groupe déroulant Boutique : Magazines + Commandes --}}
            @php
                $isShopGroupActive = request()->routeIs('admin.shop.*') || request()->routeIs('admin.orders.*');
            @endphp
            <div x-data="{ open: @js($isShopGroupActive) }" class="space-y-1">
                <button type="button" @click="open = !open"
                    class="w-full flex items-center justify-between px-3 py-2 rounded-lg transition cursor-pointer {{ $isShopGroupActive ? 'bg-slate-900 text-white font-semibold' : 'hover:bg-slate-900 hover:text-white text-slate-300' }}">
                    <span class="flex items-center gap-2.5">
                        <i data-lucide="shopping-bag"
                            class="w-4 h-4 shrink-0 {{ $isShopGroupActive ? 'text-red-500' : '' }}"></i>
                        <span>Boutique</span>
                    </span>
                    <i data-lucide="chevron-down" class="w-3.5 h-3.5 transition-transform duration-200"
                        :class="open ? 'rotate-180 text-red-500' : 'text-slate-500'"></i>
                </button>

                <div x-show="open" x-collapse class="pl-4 pr-1 py-1 space-y-0.5 border-l border-slate-800 ml-4">
                    <a href="{{ route('admin.shop.index') }}"
                        class="flex items-center gap-2 px-2.5 py-1.5 rounded-md text-xs transition {{ request()->routeIs('admin.shop.*') ? 'bg-red-700 text-white font-semibold shadow-2xs' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                        <i data-lucide="book-open" class="w-3.5 h-3.5 shrink-0"></i>
                        <span>Magazines & Livres</span>
                    </a>

                    <a href="{{ route('admin.orders.index') }}"
                        class="flex items-center justify-between px-2.5 py-1.5 rounded-md text-xs transition {{ request()->routeIs('admin.orders.*') ? 'bg-red-700 text-white font-semibold shadow-2xs' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                        <span class="flex items-center gap-2">
                            <i data-lucide="receipt" class="w-3.5 h-3.5 shrink-0"></i>
                            <span>Commandes</span>
                        </span>
                        <span
                            class="px-1.5 py-0.2 text-[10px] font-bold rounded-full {{ request()->routeIs('admin.orders.*') ? 'bg-white/20 text-white' : 'bg-slate-800 text-slate-300' }}">
                            {{ \App\Models\Order::count() }}
                        </span>
                    </a>
                </div>
            </div>

            <a href="{{ route('admin.subscriptions.index') }}"
                class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.subscriptions.*') ? 'bg-red-700 text-white font-semibold shadow-xs' : 'hover:bg-slate-900 hover:text-white' }}">
                <i data-lucide="crown" class="w-4 h-4 shrink-0 text-amber-400"></i>
                <span>Abonnements Premium</span>
            </a>

            <a href="{{ route('admin.payment-methods.index') }}"
                class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.payment-methods.*') ? 'bg-red-700 text-white font-semibold shadow-xs' : 'hover:bg-slate-900 hover:text-white' }}">
                <i data-lucide="credit-card" class="w-4 h-4 shrink-0 text-sky-400"></i>
                <span>Méthodes de paiement</span>
            </a>

            <a href="{{ route('admin.events.index') }}"
                class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.events.*') ? 'bg-red-700 text-white font-semibold shadow-xs' : 'hover:bg-slate-900 hover:text-white' }}">
                <i data-lucide="calendar-clock" class="w-4 h-4 shrink-0"></i>
                <span>Événements & Forums</span>
            </a>

            <a href="{{ route('admin.podcasts.index') }}"
                class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.podcasts.*') ? 'bg-red-700 text-white font-semibold shadow-xs' : 'hover:bg-slate-900 hover:text-white' }}">
                <i data-lucide="mic" class="w-4 h-4 shrink-0"></i>
                <span>Podcasts & WebTV</span>
            </a>

            <a href="{{ route('admin.social.index') }}"
                class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.social.*') ? 'bg-red-700 text-white font-semibold shadow-xs' : 'hover:bg-slate-900 hover:text-white' }}">
                <i data-lucide="share-2" class="w-4 h-4 shrink-0"></i>
                <span>Réseaux Sociaux (API)</span>
            </a>

            <div class="px-3 pt-3 pb-1 text-[10px] font-semibold uppercase tracking-wider text-slate-500">
                Architecture & Système
            </div>

            <a href="{{ route('admin.users.index') }}"
                class="flex items-center justify-between px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.users.*') ? 'bg-red-700 text-white font-semibold shadow-xs' : 'hover:bg-slate-900 hover:text-white' }}">
                <span class="flex items-center gap-2.5">
                    <i data-lucide="users" class="w-4 h-4 shrink-0"></i>
                    <span>Utilisateurs</span>
                </span>
                <span
                    class="px-1.5 py-0.2 text-[10px] font-bold rounded-full {{ request()->routeIs('admin.users.*') ? 'bg-white/20 text-white' : 'bg-slate-800 text-slate-300' }}">
                    {{ \App\Models\User::count() }}
                </span>
            </a>

            <a href="{{ route('admin.menus.index') }}"
                class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.menus.*') ? 'bg-red-700 text-white font-semibold shadow-xs' : 'hover:bg-slate-900 hover:text-white' }}">
                <i data-lucide="compass" class="w-4 h-4 shrink-0"></i>
                <span>Menus</span>
            </a>

            <a href="{{ route('admin.settings.index') }}"
                class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.settings.*') ? 'bg-red-700 text-white font-semibold shadow-xs' : 'hover:bg-slate-900 hover:text-white' }}">
                <i data-lucide="settings" class="w-4 h-4 shrink-0"></i>
                <span>Configuration & 301</span>
            </a>
        </nav>

        <!-- Pied de Sidebar fixe -->
        <div class="p-4 border-t border-slate-800 space-y-3 text-xs shrink-0 bg-slate-950">
            <a href="{{ route('magazine.home') }}" target="_blank"
                class="flex items-center justify-between px-3 py-2 rounded-lg bg-slate-900 text-slate-200 hover:bg-slate-800 transition">
                <span class="flex items-center gap-2">
                    <i data-lucide="globe" class="w-3.5 h-3.5 text-red-500"></i>
                    <span>Voir le Magazine</span>
                </span>
                <i data-lucide="external-link" class="w-3.5 h-3.5 text-slate-400"></i>
            </a>
            <div class="flex items-center justify-between text-slate-400 px-1">
                <span class="truncate flex items-center gap-1.5">
                    <i data-lucide="user" class="w-3.5 h-3.5 text-emerald-400 shrink-0"></i>
                    <span class="truncate">{{ auth()->user()->display_name }}</span>
                </span>
            </div>
        </div>
    </aside>

    <!-- Main Workspace (Scrollable indépendamment) -->
    <div class="flex-1 flex flex-col min-w-0 h-screen overflow-y-auto">
        <header
            class="bg-white border-b border-slate-200 px-8 py-4 flex flex-wrap items-center justify-between gap-4 sticky top-0 z-30">
            <div>
                <h1 class="text-xl font-bold text-slate-900">
                    @yield('header_title', View::getSection('title', 'Studio Rédaction'))
                </h1>
                @hasSection('subtitle')
                    <p class="text-xs text-slate-500 mt-0.5">@yield('subtitle')</p>
                @endif
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                @hasSection('top_actions')
                    @yield('top_actions')
                    <span class="h-5 w-px bg-slate-200 mx-1"></span>
                @endif

                <button type="button" onclick="window.openMediaManager && window.openMediaManager()"
                    class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold rounded-lg transition inline-flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="image" class="w-3.5 h-3.5 text-red-700"></i>
                    <span>Médiathèque</span>
                </button>

                <a href="{{ route('admin.posts.create', ['type' => 'page']) }}"
                    class="px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-lg transition inline-flex items-center gap-1.5">
                    <i data-lucide="layout-template" class="w-3.5 h-3.5"></i>
                    <span>Nouvelle Page</span>
                </a>

                <a href="{{ route('admin.posts.create', ['type' => 'post']) }}"
                    class="px-4 py-2 bg-red-700 hover:bg-red-800 text-white text-xs font-bold uppercase tracking-wider rounded-lg transition inline-flex items-center gap-1.5">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>Nouvel Article</span>
                </a>

                <span class="h-6 w-px bg-slate-200 mx-1"></span>

                <!-- Menu Déroulant Profil Utilisateur Connecté -->
                <div x-data="{ userMenuOpen: false }" class="relative" @click.outside="userMenuOpen = false">
                    <button type="button" @click="userMenuOpen = !userMenuOpen"
                        class="flex items-center gap-2.5 p-1 pl-2 pr-2.5 rounded-xl border border-slate-200 hover:border-slate-300 bg-white hover:bg-slate-50 transition shadow-2xs cursor-pointer focus:outline-none">
                        @if(auth()->user()->avatar_url)
                            <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->display_name }}"
                                class="w-7 h-7 rounded-lg object-cover border border-slate-200 shrink-0">
                        @else
                            <div
                                class="w-7 h-7 rounded-lg bg-red-700 text-white font-extrabold text-[11px] flex items-center justify-center shrink-0">
                                {{ auth()->user()->initials() }}
                            </div>
                        @endif

                        <i data-lucide="chevron-down"
                            class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200"
                            :class="userMenuOpen ? 'rotate-180 text-slate-700' : ''"></i>
                    </button>

                    <!-- Dropdown Panel -->
                    <div x-show="userMenuOpen" x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                        x-transition:leave-end="opacity-0 scale-95 -translate-y-1" x-cloak
                        class="absolute right-0 mt-2 w-64 bg-white border border-slate-200 rounded-2xl shadow-xl py-2 z-50 overflow-hidden">
                        <!-- En-tête du menu déroulant -->
                        <div class="px-4 py-3 border-b border-slate-100 bg-slate-50/60">
                            <div class="text-xs font-bold text-slate-900 truncate">{{ auth()->user()->display_name }}
                            </div>
                            <div class="text-[11px] text-slate-500 truncate">{{ auth()->user()->email }}</div>
                            <div class="mt-1.5 flex items-center gap-1.5">
                                <span
                                    class="px-2 py-0.5 text-[9px] font-bold uppercase rounded-md bg-red-100 text-red-700">
                                    {{ auth()->user()->role?->name ?? 'Membre' }}
                                </span>
                                <span
                                    class="px-2 py-0.5 text-[9px] font-bold uppercase rounded-md bg-emerald-100 text-emerald-800">
                                    {{ auth()->user()->status->label() }}
                                </span>
                            </div>
                        </div>

                        <!-- Liens du menu déroulant -->
                        <div class="py-1">
                            <a href="{{ route('admin.profile.edit') }}"
                                class="flex items-center gap-2.5 px-4 py-2.5 text-xs text-slate-700 hover:bg-slate-50 hover:text-red-700 transition font-medium">
                                <i data-lucide="user" class="w-4 h-4 text-slate-400"></i>
                                <div>
                                    <span class="font-bold block">Mon Profil</span>
                                    <span class="text-[10px] text-slate-400">Modifier coordonnées & mot de passe</span>
                                </div>
                            </a>

                            <a href="{{ route('admin.users.index') }}"
                                class="flex items-center gap-2.5 px-4 py-2.5 text-xs text-slate-700 hover:bg-slate-50 hover:text-red-700 transition font-medium">
                                <i data-lucide="users" class="w-4 h-4 text-slate-400"></i>
                                <div>
                                    <span class="font-bold block">Gestion des Utilisateurs</span>
                                    <span class="text-[10px] text-slate-400">Comptes, rôles & activations</span>
                                </div>
                            </a>

                            <a href="{{ route('magazine.home') }}" target="_blank"
                                class="flex items-center gap-2.5 px-4 py-2.5 text-xs text-slate-700 hover:bg-slate-50 hover:text-red-700 transition font-medium">
                                <i data-lucide="external-link" class="w-4 h-4 text-slate-400"></i>
                                <div>
                                    <span class="font-bold block">Voir le site public</span>
                                    <span class="text-[10px] text-slate-400">Ouvrir le magazine en ligne</span>
                                </div>
                            </a>
                        </div>

                        <!-- Séparateur & Déconnexion sécurisée -->
                        <div class="border-t border-slate-100 pt-1">
                            <button type="button" @click="userMenuOpen = false; window.confirmAction({
                                    title: 'Déconnexion',
                                    message: 'Êtes-vous sûr de vouloir vous déconnecter de votre session d\'administration CIOMAG ?',
                                    formId: 'admin-header-logout-form',
                                    confirmText: 'Se déconnecter',
                                    variant: 'danger',
                                    icon: 'log-out'
                                })"
                                class="w-full flex items-center gap-2.5 px-4 py-2.5 text-xs text-rose-600 hover:bg-rose-50 transition font-bold cursor-pointer text-left">
                                <i data-lucide="log-out" class="w-4 h-4 text-rose-500"></i>
                                <span>Déconnexion</span>
                            </button>
                        </div>
                    </div>
                </div>

                <form id="admin-header-logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                    @csrf
                </form>
            </div>
        </header>

        <main class="p-8 flex-1">
            @if(session('success'))
                <div
                    class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium flex items-center gap-2">
                    <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div
                    class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-medium flex items-center gap-2">
                    <i data-lucide="alert-triangle" class="w-4 h-4 text-rose-600 shrink-0"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif
            @if($errors->any())
                <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <!-- Composant Universel Media Manager & Cropper -->
    <x-media-manager-modal />

    <!-- Composant Réutilisable Dialog de Confirmation d'Actions Critiques -->
    <x-ui.confirm-dialog />

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) {
                window.lucide.createIcons();
            }
        });
    </script>

    @stack('scripts')
</body>

</html>