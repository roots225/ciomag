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

    @if(!empty($siteGeneral['favicon_url']))
        <link rel="icon" href="{{ $siteGeneral['favicon_url'] }}">
        <link rel="apple-touch-icon" href="{{ $siteGeneral['favicon_url'] }}">
    @endif

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
    class="bg-slate-50 text-slate-900 antialiased selection:bg-ciomag-teal selection:text-white flex flex-col min-h-screen">

    <!-- Top Utility Bar -->
    <div class="bg-ciomag-slate-dark text-slate-300 text-xs border-b border-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-10 flex items-center justify-between">
            <!-- Left: Edition, Date, Quick Links -->
            <div class="flex items-center space-x-3 sm:space-x-4">
                <span class="hidden md:inline text-[11px] text-slate-400">
                    {{ now()->locale('fr')->isoFormat('dddd D MMMM YYYY') }}
                </span>
                <span class="hidden lg:inline text-slate-600">|</span>
                <a href="{{ route('magazine.page', 'a-propos') }}"
                    class="hidden lg:inline text-[11px] uppercase font-bold tracking-wider text-slate-300 hover:text-ciomag-amber transition">Qui
                    sommes-nous ?</a>
                <a href="{{ route('magazine.page', 'charte-editoriale') }}"
                    class="hidden xl:inline text-[11px] uppercase font-bold tracking-wider text-slate-300 hover:text-ciomag-amber transition">Charte
                    éditoriale</a>
            </div>

            <!-- Right: Social Media Icons & Auth -->
            <div class="flex items-center space-x-3">
                <!-- Social Media Buttons (as seen in official CIOMAG header) -->
                <div class="flex items-center space-x-1.5">
                    <a href="https://facebook.com" target="_blank" rel="noopener noreferrer"
                        class="w-7 h-7 rounded bg-[#2f3d3b] hover:bg-ciomag-teal text-white flex items-center justify-center transition"
                        title="Facebook">
                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 320 512" aria-hidden="true">
                            <path d="M279.14 288l14.22-92.66h-88.91v-60.13c0-25.35 12.42-50.06 52.24-50.06h40.42V6.26S260.43 0 225.36 0c-73.22 0-121.08 44.38-121.08 124.72v70.62H22.89V288h81.39v224h100.17V288z"/>
                        </svg>
                    </a>
                    <a href="https://twitter.com" target="_blank" rel="noopener noreferrer"
                        class="w-7 h-7 rounded bg-[#2f3d3b] hover:bg-ciomag-teal text-white flex items-center justify-center transition"
                        title="X / Twitter">
                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 512 512" aria-hidden="true">
                            <path d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z"/>
                        </svg>
                    </a>
                    <a href="https://linkedin.com" target="_blank" rel="noopener noreferrer"
                        class="w-7 h-7 rounded bg-[#2f3d3b] hover:bg-ciomag-teal text-white flex items-center justify-center transition"
                        title="LinkedIn">
                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 448 512" aria-hidden="true">
                            <path d="M100.28 448H7.4V148.9h92.88zM53.79 108.1C24.09 108.1 0 83.5 0 53.8a53.79 53.79 0 0 1 107.58 0c0 29.7-24.1 54.3-53.79 54.3zM447.9 448h-92.68V302.4c0-34.7-.7-79.2-48.29-79.2-48.3 0-55.7 37.7-55.7 76.7V448h-92.78V148.9h89.08v40.8h1.3c12.4-23.5 42.7-48.3 87.9-48.3 94 0 111.3 61.9 111.3 142.3V448z"/>
                        </svg>
                    </a>
                    <a href="https://youtube.com" target="_blank" rel="noopener noreferrer"
                        class="w-7 h-7 rounded bg-[#2f3d3b] hover:bg-ciomag-teal text-white flex items-center justify-center transition"
                        title="YouTube">
                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 576 512" aria-hidden="true">
                            <path d="M549.655 124.083c-6.281-23.65-24.787-42.276-48.284-48.597C458.781 64 288 64 288 64S117.22 64 74.629 75.486c-23.497 6.322-42.003 24.947-48.284 48.597-11.412 42.867-11.412 132.305-11.412 132.305s0 89.438 11.412 132.305c6.281 23.65 24.787 41.5 48.284 47.821C117.22 448 288 448 288 448s170.78 0 213.371-11.486c23.497-6.321 42.003-24.171 48.284-47.821 11.412-42.867 11.412-132.305 11.412-132.305s0-89.438-11.412-132.305zm-317.51 213.583V175.185l142.739 81.205-142.739 81.276z"/>
                        </svg>
                    </a>
                </div>

                <span class="text-slate-700">|</span>

                @auth
                    @if(auth()->user()->isAdminOrEditor())
                        <a href="{{ route('admin.dashboard') }}"
                            class="text-ciomag-amber hover:text-amber-300 text-[11px] font-bold uppercase tracking-wider inline-flex items-center gap-1">
                            <i data-lucide="sliders" class="w-3.5 h-3.5"></i>
                            <span class="hidden sm:inline">Studio Rédaction</span>
                        </a>
                    @endif
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit"
                            class="text-slate-400 hover:text-white text-[11px] uppercase font-bold tracking-wider transition inline-flex items-center gap-1 cursor-pointer">
                            <i data-lucide="log-out" class="w-3.5 h-3.5"></i>
                            <span class="hidden sm:inline">Déconnexion</span>
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}"
                        class="text-slate-300 hover:text-ciomag-amber text-[11px] font-bold uppercase tracking-wider transition inline-flex items-center gap-1">
                        <i data-lucide="lock" class="w-3 h-3"></i>
                        <span>Accès Rédaction</span>
                    </a>
                @endauth
            </div>
        </div>
    </div>

    <!-- Magazine Masthead (White Middle Section with Logo + ALERTE INFOS) -->
    <div class="bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-5 flex items-center justify-between gap-4">
            <!-- Brand Logo -->
            <div class="flex items-center gap-4">
                <a href="{{ route('magazine.home') }}" class="group flex items-center gap-2">
                    @if(!empty($siteGeneral['logo_url']))
                        <img src="{{ $siteGeneral['logo_url'] }}" alt="{{ $siteGeneral['site_name'] ?? 'CIOMAG' }}"
                            class="h-10 sm:h-12 w-auto max-w-[240px] object-contain">
                    @else
                        <span
                            class="text-3xl sm:text-4xl font-black tracking-tighter text-ciomag-pine group-hover:text-ciomag-teal transition">CIO</span>
                        <span class="text-3xl sm:text-4xl font-light tracking-tight text-ciomag-slate-light">MAG</span>
                    @endif
                </a>
                <p class="hidden xl:block text-xs text-slate-500 border-l border-slate-200 pl-4 max-w-xs leading-snug">
                    {{ $siteGeneral['tagline'] ?? 'Le Magazine des Dirigeants IT & de la Transformation Digitale' }}
                </p>
            </div>

            <!-- Middle: ALERTE INFOS Ticker (Matching Image 2) -->
            <div class="hidden md:flex items-center gap-3 bg-slate-50 border border-slate-200/80 rounded-lg p-1.5 flex-1 max-w-xl mx-4"
                x-data="{
                    current: 0,
                    items: {{ json_encode($breakingNews->map(fn($p) => ['title' => $p->title, 'url' => route('magazine.post', $p->slug)])) }}
                }">
                <!-- Crimson Alerte Infos Badge -->
                <span
                    class="shrink-0 px-3 py-1.5 bg-ciomag-crimson text-white text-[11px] font-black uppercase tracking-wider rounded flex items-center gap-1.5 shadow-2xs">
                    <i data-lucide="zap" class="w-3.5 h-3.5 fill-current text-ciomag-amber"></i>
                    <span>ALERTE INFOS</span>
                </span>

                <!-- News Title -->
                <div class="flex-1 overflow-hidden">
                    <template x-if="items.length > 0">
                        <a :href="items[current].url" x-text="items[current].title"
                            class="block text-xs font-bold text-slate-800 hover:text-ciomag-teal truncate transition">
                        </a>
                    </template>
                    <template x-if="items.length === 0">
                        <span class="block text-xs font-semibold text-slate-600 truncate">
                            Transformation digitale & souveraineté des données en Afrique
                        </span>
                    </template>
                </div>

                <!-- Prev/Next Controls -->
                <div class="flex items-center gap-1 shrink-0" x-show="items.length > 1">
                    <button type="button" @click="current = (current - 1 + items.length) % items.length"
                        class="p-1 text-slate-400 hover:text-slate-800 hover:bg-slate-200 rounded transition cursor-pointer"
                        aria-label="Article précédent">
                        <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
                    </button>
                    <button type="button" @click="current = (current + 1) % items.length"
                        class="p-1 text-slate-400 hover:text-slate-800 hover:bg-slate-200 rounded transition cursor-pointer"
                        aria-label="Article suivant">
                        <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                    </button>
                </div>
            </div>

            <!-- Right Actions: Boutique & S'abonner -->
            <div class="flex items-center gap-2.5 shrink-0">
                <a href="{{ route('magazine.shop.index') }}"
                    class="hidden sm:inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold uppercase tracking-wider bg-slate-100 hover:bg-ciomag-teal hover:text-white text-slate-800 rounded transition">
                    <i data-lucide="shopping-bag" class="w-3.5 h-3.5 text-ciomag-amber"></i>
                    <span>Boutique</span>
                </a>

                <a href="{{ route('magazine.subscriptions.index') }}"
                    class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-black uppercase tracking-wider bg-ciomag-amber hover:bg-ciomag-amber-hover text-slate-950 rounded shadow-xs transition">
                    <i data-lucide="crown" class="w-3.5 h-3.5"></i>
                    <span>S'abonner</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Navigation Bar (Full-width bg-ciomag-slate #506361 as shown in Image 2) -->
    <header class="bg-ciomag-slate sticky top-0 z-40 shadow-md" x-data="{ mobileOpen: false, searchOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-12">
                <!-- Desktop Navigation Menu -->
                <nav class="hidden lg:flex items-center space-x-1 flex-1 overflow-x-auto no-scrollbar">
                    @foreach($headerMenu as $item)
                        @php
                            $hasChildren = !empty($item['children']);
                            $isActive = request()->url() === $item['resolved_url']
                                || (request()->routeIs('magazine.home') && in_array(strtolower($item['label']), ['accueil', 'home']));
                        @endphp
                        <div class="relative shrink-0" x-data="{ open: false }" @mouseenter="open = true"
                            @mouseleave="open = false">
                            <a href="{{ $item['resolved_url'] }}" target="{{ $item['target'] ?? '_self' }}"
                                @if(!empty($item['title_attr'])) title="{{ $item['title_attr'] }}" @endif
                                class="px-3.5 py-3 text-xs sm:text-[13px] font-bold uppercase tracking-wider inline-flex items-center gap-1.5 transition border-b-2 {{ $isActive ? 'text-ciomag-amber border-ciomag-amber font-black' : 'text-white border-transparent hover:text-ciomag-amber hover:border-ciomag-amber/40' }} {{ $item['css_classes'] ?? '' }}">
                                @if(!empty($item['icon']))
                                    <i data-lucide="{{ str_replace('lucide-', '', $item['icon']) }}" class="w-3.5 h-3.5"></i>
                                @endif
                                <span>{{ $item['label'] }}</span>
                                @if(!empty($item['badge_text']))
                                    <span
                                        class="px-1.5 py-0.2 text-[9px] font-black uppercase tracking-wider bg-ciomag-amber text-slate-950 rounded-xs">
                                        {{ $item['badge_text'] }}
                                    </span>
                                @endif
                                @if($hasChildren)
                                    <i data-lucide="chevron-down" class="w-3.5 h-3.5 opacity-70"></i>
                                @endif
                            </a>

                            @if($hasChildren)
                                <div x-show="open" x-transition.opacity.duration.150ms style="display: none;"
                                    class="absolute left-0 top-full w-60 bg-[#3f504e] border border-white/10 rounded-b-xl shadow-2xl py-2 z-50">
                                    @foreach($item['children'] as $child)
                                        <a href="{{ $child['resolved_url'] }}" target="{{ $child['target'] ?? '_self' }}"
                                            class="px-4 py-2.5 text-xs uppercase font-semibold text-slate-100 hover:bg-ciomag-teal hover:text-white flex items-center justify-between transition">
                                            <span>{{ $child['label'] }}</span>
                                            @if(!empty($child['badge_text']))
                                                <span
                                                    class="px-1.5 py-0.5 text-[9px] font-black uppercase bg-ciomag-amber text-slate-950 rounded-xs">
                                                    {{ $child['badge_text'] }}
                                                </span>
                                            @endif
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </nav>

                <!-- Mobile Menu Button & Search Trigger -->
                <div class="flex items-center gap-2 text-white">
                    <button @click="searchOpen = !searchOpen"
                        class="p-2 text-white hover:text-ciomag-amber transition cursor-pointer"
                        aria-label="Rechercher">
                        <i data-lucide="search" class="w-5 h-5"></i>
                    </button>

                    <button @click="mobileOpen = !mobileOpen"
                        class="lg:hidden p-2 text-white hover:text-ciomag-amber transition cursor-pointer"
                        aria-label="Menu principal">
                        <i data-lucide="menu" class="w-6 h-6"></i>
                    </button>
                </div>
            </div>

            <!-- Slide-down Search Bar -->
            <div x-show="searchOpen" x-cloak x-transition class="py-3 border-t border-white/10">
                <form action="{{ route('magazine.search') }}" method="GET" class="flex gap-2 max-w-xl mx-auto">
                    <input type="search" name="q" value="{{ request('q') }}"
                        placeholder="Rechercher un dossier, une analyse, un sujet IT..."
                        class="flex-1 px-4 py-2 text-sm bg-white border border-slate-300 rounded-lg text-slate-900 focus:outline-none focus:ring-2 focus:ring-ciomag-amber">
                    <button type="submit"
                        class="px-5 py-2 bg-ciomag-amber hover:bg-ciomag-amber-hover text-slate-950 text-xs font-black uppercase tracking-wider rounded-lg transition cursor-pointer flex items-center gap-1.5">
                        <i data-lucide="search" class="w-4 h-4"></i>
                        <span>Rechercher</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Mobile Navigation Drawer -->
        <div x-show="mobileOpen" x-cloak class="lg:hidden bg-[#3f504e] border-t border-white/10 px-4 py-4 space-y-2">
            @foreach($headerMenu as $item)
                <div>
                    <a href="{{ $item['resolved_url'] }}"
                        class="block py-2 text-sm font-bold uppercase tracking-wider text-white hover:text-ciomag-amber transition">
                        {{ $item['label'] }}
                        @if(!empty($item['badge_text']))
                            <span class="ml-1 px-1.5 py-0.5 text-[9px] bg-ciomag-amber text-slate-950 rounded-xs font-black">
                                {{ $item['badge_text'] }}
                            </span>
                        @endif
                    </a>
                    @if(!empty($item['children']))
                        <div class="pl-4 border-l-2 border-white/20 space-y-1">
                            @foreach($item['children'] as $child)
                                <a href="{{ $child['resolved_url'] }}"
                                    class="block py-1.5 text-xs text-slate-200 hover:text-ciomag-amber transition">
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
    <footer class="bg-[#182322] text-slate-300 mt-20 border-t-4 border-ciomag-teal">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10">
                <!-- Col 1: Brand -->
                <div class="space-y-4">
                    <div class="flex items-center gap-2">
                        @if(!empty($siteGeneral['logo_url']))
                            <img src="{{ $siteGeneral['logo_url'] }}" alt="{{ $siteGeneral['site_name'] ?? 'CIOMAG' }}"
                                class="h-9 w-auto max-w-[190px] object-contain brightness-0 invert">
                        @else
                            <span class="text-3xl font-black tracking-tighter text-white">CIO</span>
                            <span class="text-3xl font-light tracking-tight text-ciomag-amber">MAG</span>
                        @endif
                    </div>
                    <p class="text-sm text-slate-300 leading-relaxed">
                        {{ $siteGeneral['footer_about'] ?? 'CIOMAG est le média de référence dédié aux Directeurs des Systèmes d\'Information (DSI), RSSI, CDO et décideurs technologiques.' }}
                    </p>
                    <div class="pt-2 text-xs text-slate-400 flex items-center gap-2">
                        <i data-lucide="mail" class="w-4 h-4 text-ciomag-amber"></i>
                        <span>Contact : <span class="text-slate-200 font-medium">{{ $siteGeneral['contact_email'] ??
                                'redaction@ciomag.com' }}</span></span>
                    </div>

                    <div class="pt-3 flex items-center space-x-2">
                        <a href="https://facebook.com" target="_blank" rel="noopener noreferrer"
                            class="w-8 h-8 rounded-lg bg-white/10 hover:bg-ciomag-teal text-white flex items-center justify-center transition"
                            title="Facebook">
                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 320 512" aria-hidden="true">
                                <path d="M279.14 288l14.22-92.66h-88.91v-60.13c0-25.35 12.42-50.06 52.24-50.06h40.42V6.26S260.43 0 225.36 0c-73.22 0-121.08 44.38-121.08 124.72v70.62H22.89V288h81.39v224h100.17V288z"/>
                            </svg>
                        </a>
                        <a href="https://twitter.com" target="_blank" rel="noopener noreferrer"
                            class="w-8 h-8 rounded-lg bg-white/10 hover:bg-ciomag-teal text-white flex items-center justify-center transition"
                            title="X / Twitter">
                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 512 512" aria-hidden="true">
                                <path d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z"/>
                            </svg>
                        </a>
                        <a href="https://linkedin.com" target="_blank" rel="noopener noreferrer"
                            class="w-8 h-8 rounded-lg bg-white/10 hover:bg-ciomag-teal text-white flex items-center justify-center transition"
                            title="LinkedIn">
                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 448 512" aria-hidden="true">
                                <path d="M100.28 448H7.4V148.9h92.88zM53.79 108.1C24.09 108.1 0 83.5 0 53.8a53.79 53.79 0 0 1 107.58 0c0 29.7-24.1 54.3-53.79 54.3zM447.9 448h-92.68V302.4c0-34.7-.7-79.2-48.29-79.2-48.3 0-55.7 37.7-55.7 76.7V448h-92.78V148.9h89.08v40.8h1.3c12.4-23.5 42.7-48.3 87.9-48.3 94 0 111.3 61.9 111.3 142.3V448z"/>
                            </svg>
                        </a>
                        <a href="https://youtube.com" target="_blank" rel="noopener noreferrer"
                            class="w-8 h-8 rounded-lg bg-white/10 hover:bg-ciomag-teal text-white flex items-center justify-center transition"
                            title="YouTube">
                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 576 512" aria-hidden="true">
                                <path d="M549.655 124.083c-6.281-23.65-24.787-42.276-48.284-48.597C458.781 64 288 64 288 64S117.22 64 74.629 75.486c-23.497 6.322-42.003 24.947-48.284 48.597-11.412 42.867-11.412 132.305-11.412 132.305s0 89.438 11.412 132.305c6.281 23.65 24.787 41.5 48.284 47.821C117.22 448 288 448 288 448s170.78 0 213.371-11.486c23.497-6.321 42.003-24.171 48.284-47.821 11.412-42.867 11.412-132.305 11.412-132.305s0-89.438-11.412-132.305zm-317.51 213.583V175.185l142.739 81.205-142.739 81.276z"/>
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Col 2: Dynamic Footer Menu 1 -->
                <div>
                    <h3
                        class="text-xs font-bold uppercase tracking-wider text-white mb-4 pb-2 border-b border-white/10">
                        Rubriques Éditoriales</h3>
                    <ul class="space-y-2.5 text-sm">
                        @forelse($footerMenu1 as $item)
                            <li>
                                <a href="{{ $item['resolved_url'] }}"
                                    class="text-slate-300 hover:text-ciomag-amber transition inline-flex items-center gap-1.5">
                                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-ciomag-amber"></i>
                                    <span>{{ $item['label'] }}</span>
                                </a>
                            </li>
                        @empty
                            @foreach($topCategories as $cat)
                                <li>
                                    <a href="{{ route('magazine.category', $cat->slug) }}"
                                        class="text-slate-300 hover:text-ciomag-amber transition inline-flex items-center gap-1.5">
                                        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-ciomag-amber"></i>
                                        <span>{{ $cat->name }}</span>
                                    </a>
                                </li>
                            @endforeach
                        @endforelse
                    </ul>
                </div>

                <!-- Col 3: Dynamic Footer Menu 2 -->
                <div>
                    <h3
                        class="text-xs font-bold uppercase tracking-wider text-white mb-4 pb-2 border-b border-white/10">
                        Le Magazine & Services</h3>
                    <ul class="space-y-2.5 text-sm">
                        @foreach($footerMenu2 as $item)
                            <li>
                                <a href="{{ $item['resolved_url'] }}"
                                    class="text-slate-300 hover:text-ciomag-amber transition inline-flex items-center gap-1.5">
                                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-ciomag-amber"></i>
                                    <span>{{ $item['label'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Col 4: Newsletter -->
                <div>
                    <h3
                        class="text-xs font-bold uppercase tracking-wider text-white mb-4 pb-2 border-b border-white/10">
                        Le Brief des DSI</h3>
                    <p class="text-sm text-slate-300 mb-4">
                        Recevez chaque semaine nos enquêtes exclusives sur l'IA, le Cloud souverain et la Cybersécurité.
                    </p>
                    <form action="{{ route('magazine.search') }}" method="GET" class="space-y-2.5">
                        <input type="email" placeholder="votre.nom@entreprise.com"
                            class="w-full px-3.5 py-2 text-sm bg-[#101918] border border-white/15 rounded-lg text-white placeholder-slate-400 focus:outline-none focus:border-ciomag-amber">
                        <button type="button" onclick="alert('Inscription enregistrée au Brief Hebdomadaire CIOMAG !')"
                            class="w-full py-2.5 bg-ciomag-amber hover:bg-ciomag-amber-hover text-slate-950 text-xs font-black uppercase tracking-wider rounded-lg transition inline-flex items-center justify-center gap-1.5 cursor-pointer shadow-xs">
                            <i data-lucide="send" class="w-3.5 h-3.5"></i>
                            <span>S'abonner gratuitement</span>
                        </button>
                    </form>
                </div>
            </div>

            <div
                class="mt-12 pt-8 border-t border-white/10 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-400 gap-4">
                <p>© {{ date('Y') }} {{ $siteGeneral['site_name'] ?? 'CIOMAG' }}. Tous droits réservés.</p>
                <div class="flex items-center gap-4">
                    <a href="{{ route('magazine.page', 'a-propos') }}" class="hover:text-ciomag-amber transition">À
                        propos</a>
                    <span>•</span>
                    <a href="{{ route('magazine.page', 'charte-editoriale') }}"
                        class="hover:text-ciomag-amber transition">Mentions légales</a>
                </div>
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