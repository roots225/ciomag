<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>
        @yield('meta_title', ($siteGeneral['site_name'] ?? 'CIOMAG') . ' — ' . ($siteGeneral['tagline'] ?? 'Magazine IT & Transformation Digitale'))
    </title>
    <meta name="description"
        content="@yield('meta_description', $siteGeneral['tagline'] ?? 'Le Magazine des Dirigeants IT & de la Transformation Digitale')">

    @hasSection('canonical_url')
        <link rel="canonical" href="@yield('canonical_url')">
    @endif

    @hasSection('meta_robots')
        <meta name="robots" content="@yield('meta_robots')">
    @endif

    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:title" content="@yield('meta_title', $siteGeneral['site_name'] ?? 'CIOMAG')">
    <meta property="og:description" content="@yield('meta_description', $siteGeneral['tagline'] ?? '')">
    @hasSection('og_image')
        <meta property="og:image" content="@yield('og_image')">
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@latest"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body
    class="bg-slate-50 text-slate-900 antialiased selection:bg-red-700 selection:text-white flex flex-col min-h-screen">

    <!-- Top Utility Bar -->
    <div class="bg-slate-950 text-slate-300 text-xs border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-9 flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <span class="inline-flex items-center gap-1.5 font-semibold text-white">
                    <span class="w-2 h-2 rounded-full bg-red-600 animate-pulse"></span>
                    {{ $siteGeneral['edition'] ?? 'Édition Panafricaine & Internationale' }}
                </span>
                <span class="hidden md:inline text-slate-500">|</span>
                <span class="hidden md:inline text-slate-400">
                    {{ now()->locale('fr')->isoFormat('dddd D MMMM YYYY') }}
                </span>
            </div>

            <div class="flex items-center space-x-4">
                <a href="{{ route('magazine.page', 'a-propos') }}" class="hover:text-white transition">À propos</a>
                <a href="{{ route('magazine.page', 'charte-editoriale') }}"
                    class="hover:text-white transition hidden sm:inline">Charte éditoriale</a>
                <span class="text-slate-700">|</span>
                @auth
                    @if(auth()->user()->isAdminOrEditor())
                        <a href="{{ route('admin.dashboard') }}"
                            class="text-amber-400 hover:text-amber-300 font-semibold inline-flex items-center gap-1.5">
                            <i data-lucide="Sliders" class="w-3.5 h-3.5"></i>
                            <span>Studio Rédaction</span>
                        </a>
                    @endif
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit"
                            class="text-slate-400 hover:text-white transition inline-flex items-center gap-1 cursor-pointer">
                            <i data-lucide="log-out" class="w-3.5 h-3.5"></i>
                            <span>Déconnexion ({{ auth()->user()->display_name }})</span>
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}"
                        class="text-slate-300 hover:text-white font-medium transition inline-flex items-center gap-1">
                        <i data-lucide="lock" class="w-3 h-3"></i>
                        <span>Accès Rédaction</span>
                    </a>
                @endauth
            </div>
        </div>
    </div>

    <!-- Magazine Masthead -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-40 shadow-xs"
        x-data="{ mobileOpen: false, searchOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="py-5 flex items-center justify-between gap-4">
                <!-- Brand Logo -->
                <div class="flex items-center gap-4">
                    <a href="{{ route('magazine.home') }}" class="group flex items-baseline gap-1">
                        <span
                            class="text-3xl sm:text-4xl font-black tracking-tighter text-slate-950 group-hover:text-red-700 transition">CIO</span>
                        <span class="text-3xl sm:text-4xl font-light tracking-tight text-red-700">MAG</span>
                        <span
                            class="ml-2 px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-widest bg-slate-900 text-white rounded-xs">Digital</span>
                    </a>
                    <p
                        class="hidden lg:block text-xs text-slate-500 border-l border-slate-200 pl-4 max-w-xs leading-snug">
                        {{ $siteGeneral['tagline'] ?? 'Le Magazine des Dirigeants IT & de la Transformation Digitale' }}
                    </p>
                </div>

                <!-- Search Form & Actions -->
                <div class="flex items-center gap-2.5">
                    <form action="{{ route('magazine.search') }}" method="GET"
                        class="hidden md:flex items-center relative">
                        <input type="search" name="q" value="{{ request('q') }}"
                            placeholder="Rechercher un dossier, un sujet..."
                            class="w-56 lg:w-64 pl-3.5 pr-9 py-1.5 text-sm bg-slate-100 border border-slate-200 rounded-full focus:outline-none focus:ring-2 focus:ring-red-700 focus:bg-white transition">
                        <button type="submit" class="absolute right-3 text-slate-400 hover:text-red-700 cursor-pointer"
                            aria-label="Rechercher">
                            <i data-lucide="search" class="w-4 h-4"></i>
                        </button>
                    </form>

                    <a href="{{ route('magazine.shop.index') }}"
                        class="hidden sm:inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold uppercase tracking-wider bg-slate-100 hover:bg-slate-200 text-slate-900 rounded-full transition">
                        <i data-lucide="shopping-bag" class="w-3.5 h-3.5 text-red-700"></i>
                        <span>Boutique</span>
                    </a>

                    <a href="{{ route('magazine.subscriptions.index') }}"
                        class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-extrabold uppercase tracking-wider bg-amber-400 hover:bg-amber-300 text-slate-950 rounded-full shadow-2xs transition">
                        <i data-lucide="crown" class="w-3.5 h-3.5"></i>
                        <span>S'abonner</span>
                    </a>

                    <button @click="searchOpen = !searchOpen" class="md:hidden p-2 text-slate-600 hover:text-slate-900"
                        aria-label="Ouvrir la recherche">
                        <i data-lucide="search" class="w-5 h-5"></i>
                    </button>

                    <button @click="mobileOpen = !mobileOpen" class="lg:hidden p-2 text-slate-700 hover:text-slate-950"
                        aria-label="Menu">
                        <i data-lucide="menu" class="w-6 h-6"></i>
                    </button>
                </div>
            </div>

            <!-- Mobile Search Drawer -->
            <div x-show="searchOpen" x-cloak class="md:hidden pb-4">
                <form action="{{ route('magazine.search') }}" method="GET" class="flex gap-2">
                    <input type="search" name="q" value="{{ request('q') }}"
                        placeholder="Rechercher dans le magazine..."
                        class="flex-1 px-4 py-2 text-sm border border-slate-300 rounded-lg">
                    <button type="submit"
                        class="px-4 py-2 bg-red-700 text-white text-sm font-semibold rounded-lg">OK</button>
                </form>
            </div>

            <!-- Primary Navigation (Resolved dynamically via Recursive CTE) -->
            <nav
                class="hidden lg:flex items-center justify-between border-t border-slate-100 py-2.5 text-sm font-semibold text-slate-800">
                <ul class="flex items-center space-x-1">
                    @foreach($headerMenu as $item)
                        @php
                            $hasChildren = !empty($item['children']);
                        @endphp
                        <li class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                            <a href="{{ $item['resolved_url'] }}" target="{{ $item['target'] ?? '_self' }}"
                                @if(!empty($item['title_attr'])) title="{{ $item['title_attr'] }}" @endif
                                class="px-3 py-1.5 rounded-md hover:bg-slate-100 hover:text-red-700 transition inline-flex items-center gap-1.5 {{ $item['css_classes'] ?? '' }}">
                                @if(!empty($item['icon']))
                                    <i data-lucide="{{ str_replace('lucide-', '', $item['icon']) }}" class="w-3.5 h-3.5"></i>
                                @endif
                                <span>{{ $item['label'] }}</span>
                                @if(!empty($item['badge_text']))
                                    <span
                                        class="px-1.5 py-0.2 text-[10px] font-bold uppercase tracking-wider bg-red-700 text-white rounded-xs">
                                        {{ $item['badge_text'] }}
                                    </span>
                                @endif
                                @if($hasChildren)
                                    <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-400"></i>
                                @endif
                            </a>

                            @if($hasChildren)
                                <div x-show="open" x-transition.opacity.duration.150ms style="display: none;"
                                    class="absolute left-0 mt-1 w-64 bg-white border border-slate-200 rounded-xl shadow-xl py-2 z-50">
                                    @foreach($item['children'] as $child)
                                        <a href="{{ $child['resolved_url'] }}" target="{{ $child['target'] ?? '_self' }}"
                                            class="px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 hover:text-red-700 flex items-center justify-between transition">
                                            <span>{{ $child['label'] }}</span>
                                            @if(!empty($child['badge_text']))
                                                <span
                                                    class="px-1.5 py-0.5 text-[10px] font-bold uppercase bg-amber-100 text-amber-800 rounded-xs">
                                                    {{ $child['badge_text'] }}
                                                </span>
                                            @endif
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </nav>
        </div>

        <!-- Mobile Menu -->
        <div x-show="mobileOpen" x-cloak class="lg:hidden bg-white border-t border-slate-200 px-4 py-4 space-y-2">
            @foreach($headerMenu as $item)
                <div>
                    <a href="{{ $item['resolved_url'] }}"
                        class="block py-2 font-semibold text-slate-900 hover:text-red-700">
                        {{ $item['label'] }}
                        @if(!empty($item['badge_text']))
                            <span
                                class="ml-1 px-1.5 py-0.5 text-[10px] bg-red-700 text-white rounded-xs">{{ $item['badge_text'] }}</span>
                        @endif
                    </a>
                    @if(!empty($item['children']))
                        <div class="pl-4 border-l-2 border-slate-100 space-y-1">
                            @foreach($item['children'] as $child)
                                <a href="{{ $child['resolved_url'] }}"
                                    class="block py-1.5 text-sm text-slate-600 hover:text-red-700">
                                    {{ $child['label'] }}
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </header>

    <!-- Flash Notifications -->
    @if(session('success') || session('error') || $errors->any())
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            @if(session('success'))
                <div
                    class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium flex items-center gap-2">
                    <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div
                    class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-medium flex items-center gap-2">
                    <i data-lucide="alert-triangle" class="w-4 h-4 text-rose-600 shrink-0"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif
            @if($errors->any())
                <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    @endif

    <!-- Main Content -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- Magazine Footer -->
    <footer class="bg-slate-950 text-slate-400 mt-20 border-t-4 border-red-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10">
                <!-- Col 1: Brand -->
                <div class="space-y-4">
                    <div class="flex items-baseline gap-1">
                        <span class="text-3xl font-black tracking-tighter text-white">CIO</span>
                        <span class="text-3xl font-light tracking-tight text-red-500">MAG</span>
                    </div>
                    <p class="text-sm text-slate-400 leading-relaxed">
                        {{ $siteGeneral['footer_about'] ?? 'CIOMAG est le média de référence dédié aux Directeurs des Systèmes d\'Information (DSI), RSSI, CDO et décideurs technologiques.' }}
                    </p>
                    <div class="pt-2 text-xs text-slate-500 flex items-center gap-1.5">
                        <i data-lucide="mail" class="w-3.5 h-3.5 text-red-500"></i>
                        <span>Contact : <span class="text-slate-300">{{ $siteGeneral['contact_email'] ??
                                'redaction@ciomag.com' }}</span></span>
                    </div>
                </div>

                <!-- Col 2: Dynamic Footer Menu 1 -->
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-white mb-4">Rubriques Éditoriales</h3>
                    <ul class="space-y-2.5 text-sm">
                        @forelse($footerMenu1 as $item)
                            <li>
                                <a href="{{ $item['resolved_url'] }}"
                                    class="hover:text-white transition inline-flex items-center gap-1.5">
                                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-600"></i>
                                    <span>{{ $item['label'] }}</span>
                                </a>
                            </li>
                        @empty
                            @foreach($topCategories as $cat)
                                <li>
                                    <a href="{{ route('magazine.category', $cat->slug) }}"
                                        class="hover:text-white transition inline-flex items-center gap-1.5">
                                        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-600"></i>
                                        <span>{{ $cat->name }}</span>
                                    </a>
                                </li>
                            @endforeach
                        @endforelse
                    </ul>
                </div>

                <!-- Col 3: Dynamic Footer Menu 2 -->
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-white mb-4">Le Magazine & Services</h3>
                    <ul class="space-y-2.5 text-sm">
                        @foreach($footerMenu2 as $item)
                            <li>
                                <a href="{{ $item['resolved_url'] }}"
                                    class="hover:text-white transition inline-flex items-center gap-1.5">
                                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-600"></i>
                                    <span>{{ $item['label'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Col 4: Newsletter -->
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-white mb-4">Le Brief des DSI</h3>
                    <p class="text-sm text-slate-400 mb-4">
                        Recevez chaque semaine nos enquêtes exclusives sur l'IA, le Cloud souverain et la Cybersécurité.
                    </p>
                    <form action="{{ route('magazine.search') }}" method="GET" class="space-y-2">
                        <input type="email" placeholder="votre.nom@entreprise.com"
                            class="w-full px-3.5 py-2 text-sm bg-slate-900 border border-slate-800 rounded-lg text-white placeholder-slate-500 focus:outline-none focus:border-red-600">
                        <button type="button" onclick="alert('Inscription enregistrée au Brief Hebdomadaire CIOMAG !')"
                            class="w-full py-2 bg-red-700 hover:bg-red-600 text-white text-sm font-semibold rounded-lg transition inline-flex items-center justify-center gap-1.5 cursor-pointer">
                            <i data-lucide="send" class="w-3.5 h-3.5"></i>
                            <span>S'abonner gratuitement</span>
                        </button>
                    </form>
                </div>
            </div>

            <div
                class="mt-12 pt-8 border-t border-slate-900 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-4">
                <p>© {{ date('Y') }} {{ $siteGeneral['site_name'] ?? 'CIOMAG' }}. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) {
                window.lucide.createIcons();
            }
        });
    </script>
</body>

</html>