@if ($paginator->total() > 0)
    @php
        $currentPerPage = (int) request('per_page', $paginator->perPage());
        $perPageChoices = collect([2, 5, 10, 15, 20, 50])->push($paginator->perPage())->unique()->sort()->values();
    @endphp
    <nav
        role="navigation"
        aria-label="Pagination Navigation"
        class="bg-white rounded-2xl border border-slate-200 px-5 py-3.5 shadow-2xs flex flex-col sm:flex-row items-center justify-between gap-4"
    >
        <!-- Informations de résultats + Sélecteur par page -->
        <div class="flex flex-wrap items-center gap-4 text-xs text-slate-600">
            <div class="flex items-center gap-1.5">
                <span class="inline-flex items-center justify-center w-6 h-6 rounded-lg bg-slate-100 text-slate-700">
                    <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                </span>
                <span>
                    Affichage de
                    <strong class="font-bold text-slate-900">{{ $paginator->firstItem() ?? 0 }}</strong>
                    à
                    <strong class="font-bold text-slate-900">{{ $paginator->lastItem() ?? 0 }}</strong>
                    sur
                    <strong class="font-bold text-red-700">{{ $paginator->total() }}</strong>
                    résultat{{ $paginator->total() > 1 ? 's' : '' }}
                </span>
                <span class="text-slate-300">•</span>
                <span class="font-semibold text-slate-500">
                    Page {{ $paginator->currentPage() }} / {{ max(1, $paginator->lastPage()) }}
                </span>
            </div>

            <!-- Choix rapide du nombre par page -->
            <div class="flex items-center gap-1.5 border-l border-slate-200 pl-4">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Par page :</span>
                <div class="inline-flex items-center gap-1">
                    @foreach ([5, 10, 20, 50] as $size)
                        <a
                            href="{{ request()->fullUrlWithQuery(['per_page' => $size, $paginator->getPageName() => 1]) }}"
                            class="px-2 py-1 rounded-lg text-[11px] font-bold transition {{ $paginator->perPage() === $size ? 'bg-slate-900 text-white' : 'bg-slate-100 hover:bg-slate-200 text-slate-600' }}"
                        >
                            {{ $size }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Contrôles de navigation par page -->
        <div class="flex items-center gap-1.5">
            {{-- Première Page --}}
            @if ($paginator->onFirstPage())
                <span
                    class="inline-flex items-center justify-center w-8 h-8 rounded-xl border border-slate-200 bg-slate-50 text-slate-300 cursor-not-allowed"
                    title="Première page"
                >
                    <i data-lucide="chevrons-left" class="w-4 h-4"></i>
                </span>
            @else
                <a
                    href="{{ $paginator->url(1) }}"
                    class="inline-flex items-center justify-center w-8 h-8 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 hover:border-slate-300 text-slate-700 transition"
                    title="Première page"
                >
                    <i data-lucide="chevrons-left" class="w-4 h-4"></i>
                </a>
            @endif

            {{-- Page Précédente --}}
            @if ($paginator->onFirstPage())
                <span
                    class="inline-flex items-center gap-1 px-3 h-8 rounded-xl border border-slate-200 bg-slate-50 text-slate-300 text-xs font-semibold cursor-not-allowed"
                >
                    <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
                    <span class="hidden md:inline">Précédent</span>
                </span>
            @else
                <a
                    href="{{ $paginator->previousPageUrl() }}"
                    rel="prev"
                    class="inline-flex items-center gap-1 px-3 h-8 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 hover:border-slate-300 text-slate-700 text-xs font-semibold transition"
                >
                    <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
                    <span class="hidden md:inline">Précédent</span>
                </a>
            @endif

            {{-- Numéros de pages --}}
            @if (isset($elements) && is_array($elements))
                @foreach ($elements as $element)
                    {{-- Séparateur "..." --}}
                    @if (is_string($element))
                        <span class="inline-flex items-center justify-center w-8 h-8 text-xs font-bold text-slate-400">
                            {{ $element }}
                        </span>
                    @endif

                    {{-- Tableau de liens --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span
                                    aria-current="page"
                                    class="inline-flex items-center justify-center min-w-[2rem] h-8 px-2.5 rounded-xl bg-red-700 text-white text-xs font-extrabold shadow-2xs"
                                >
                                    {{ $page }}
                                </span>
                            @else
                                <a
                                    href="{{ $url }}"
                                    class="inline-flex items-center justify-center min-w-[2rem] h-8 px-2.5 rounded-xl border border-slate-200 bg-white hover:bg-red-50 hover:border-red-200 hover:text-red-700 text-slate-700 text-xs font-bold transition"
                                >
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            @else
                <span
                    aria-current="page"
                    class="inline-flex items-center justify-center min-w-[2rem] h-8 px-2.5 rounded-xl bg-red-700 text-white text-xs font-extrabold shadow-2xs"
                >
                    {{ $paginator->currentPage() }}
                </span>
            @endif

            {{-- Page Suivante --}}
            @if ($paginator->hasMorePages())
                <a
                    href="{{ $paginator->nextPageUrl() }}"
                    rel="next"
                    class="inline-flex items-center gap-1 px-3 h-8 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 hover:border-slate-300 text-slate-700 text-xs font-semibold transition"
                >
                    <span class="hidden md:inline">Suivant</span>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                </a>
            @else
                <span
                    class="inline-flex items-center gap-1 px-3 h-8 rounded-xl border border-slate-200 bg-slate-50 text-slate-300 text-xs font-semibold cursor-not-allowed"
                >
                    <span class="hidden md:inline">Suivant</span>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                </span>
            @endif

            {{-- Dernière Page --}}
            @if ($paginator->hasMorePages())
                <a
                    href="{{ $paginator->url($paginator->lastPage()) }}"
                    class="inline-flex items-center justify-center w-8 h-8 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 hover:border-slate-300 text-slate-700 transition"
                    title="Dernière page"
                >
                    <i data-lucide="chevrons-right" class="w-4 h-4"></i>
                </a>
            @else
                <span
                    class="inline-flex items-center justify-center w-8 h-8 rounded-xl border border-slate-200 bg-slate-50 text-slate-300 cursor-not-allowed"
                    title="Dernière page"
                >
                    <i data-lucide="chevrons-right" class="w-4 h-4"></i>
                </span>
            @endif
        </div>
    </nav>
@endif
