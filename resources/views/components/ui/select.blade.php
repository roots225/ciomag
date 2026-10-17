@props([
    'name' => '',
    'options' => [],
    'value' => '',
    'placeholder' => 'Sélectionner une option...',
    'searchPlaceholder' => 'Rechercher...',
    'searchable' => true,
    'clearable' => false,
    'xModel' => null,
])

@php
    $normalizedOptions = [];
    foreach ($options as $key => $opt) {
        if (is_array($opt)) {
            $normalizedOptions[] = [
                'value' => (string) ($opt['value'] ?? ''),
                'label' => (string) ($opt['label'] ?? ''),
                'sublabel' => isset($opt['sublabel']) ? (string) $opt['sublabel'] : null,
            ];
        } else {
            $normalizedOptions[] = [
                'value' => (string) $key,
                'label' => (string) $opt,
                'sublabel' => null,
            ];
        }
    }
    $initialValue = (string) ($value ?? '');
@endphp

<div
    x-data="{
        open: false,
        search: '',
        selected: @js($initialValue),
        options: @js($normalizedOptions),
        placeholder: @js($placeholder),
        init() {
            @if($xModel)
                if (typeof this.{{ $xModel }} !== 'undefined') {
                    this.selected = String(this.{{ $xModel }} ?? '');
                }
                this.$watch('selected', val => {
                    this.{{ $xModel }} = val;
                });
                this.$watch('{{ $xModel }}', val => {
                    this.selected = String(val ?? '');
                });
            @endif
            this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
        },
        get selectedOption() {
            return this.options.find(o => String(o.value) === String(this.selected)) || null;
        },
        get filteredOptions() {
            const q = this.search.trim().toLowerCase();
            if (!q) return this.options;
            return this.options.filter(o =>
                o.label.toLowerCase().includes(q) ||
                (o.sublabel && o.sublabel.toLowerCase().includes(q)) ||
                o.value.toLowerCase().includes(q)
            );
        },
        selectOption(val) {
            this.selected = String(val);
            this.open = false;
            this.search = '';
            this.$dispatch('select-change', { name: '{{ $name }}', value: this.selected });
        },
        clearSelection() {
            this.selected = '';
            this.$dispatch('select-change', { name: '{{ $name }}', value: '' });
        }
    }"
    @click.outside="open = false"
    @keydown.escape.window="open = false"
    {{ $attributes->merge(['class' => 'relative w-full']) }}
>
    @if($name)
        <input type="hidden" name="{{ $name }}" :value="selected">
    @endif

    <!-- Trigger Button -->
    <button
        type="button"
        @click="open = !open; if(open) { $nextTick(() => { $refs.searchInput?.focus(); if (window.lucide) window.lucide.createIcons(); }); }"
        class="w-full px-3 py-2 text-xs bg-white border border-slate-300 rounded-lg shadow-2xs hover:border-slate-400 focus:outline-none focus:ring-2 focus:ring-red-700/15 focus:border-red-700 flex items-center justify-between gap-2 text-left transition cursor-pointer"
    >
        <span class="truncate flex items-center gap-2" :class="selectedOption ? 'text-slate-900 font-medium' : 'text-slate-400'">
            <span x-text="selectedOption ? selectedOption.label : placeholder" class="truncate"></span>
            <template x-if="selectedOption && selectedOption.sublabel">
                <span class="text-[11px] text-slate-400 font-mono truncate" x-text="selectedOption.sublabel"></span>
            </template>
        </span>

        <span class="flex items-center gap-1 shrink-0 text-slate-400">
            @if($clearable)
                <span
                    x-show="selected !== ''"
                    @click.stop="clearSelection()"
                    class="p-0.5 hover:text-rose-600 rounded-md transition"
                    title="Effacer"
                >
                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                </span>
            @endif
            <i data-lucide="chevron-down" class="w-3.5 h-3.5 transition-transform duration-200" :class="open ? 'rotate-180 text-red-700' : ''"></i>
        </span>
    </button>

    <!-- Dropdown Panel -->
    <div
        x-show="open"
        x-transition.opacity.duration.120ms
        style="display: none;"
        class="absolute z-50 mt-1 w-full min-w-[200px] bg-white border border-slate-200 rounded-lg shadow-lg overflow-hidden"
    >
        @if($searchable)
            <div class="p-2 border-b border-slate-100 bg-slate-50/70">
                <div class="relative flex items-center">
                    <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-3 pointer-events-none"></i>
                    <input
                        x-ref="searchInput"
                        x-model="search"
                        type="text"
                        placeholder="{{ $searchPlaceholder }}"
                        class="w-full pl-8 pr-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg focus:outline-none focus:border-red-700 text-slate-800"
                    >
                </div>
            </div>
        @endif

        <ul class="max-h-60 overflow-y-auto divide-y divide-slate-50 py-1 text-sm">
            <template x-for="opt in filteredOptions" :key="opt.value">
                <li>
                    <button
                        type="button"
                        @click="selectOption(opt.value)"
                        class="w-full px-3.5 py-2 text-left flex items-center justify-between gap-2 hover:bg-red-50/70 transition cursor-pointer"
                        :class="String(selected) === String(opt.value) ? 'bg-red-50/90 text-red-700 font-semibold' : 'text-slate-700'"
                    >
                        <div class="truncate">
                            <span x-text="opt.label" class="block truncate"></span>
                            <template x-if="opt.sublabel">
                                <span x-text="opt.sublabel" class="block text-[11px] text-slate-400 font-mono truncate"></span>
                            </template>
                        </div>
                        <span x-show="String(selected) === String(opt.value)" class="text-red-700 shrink-0 text-xs font-bold">✓</span>
                    </button>
                </li>
            </template>

            <li x-show="filteredOptions.length === 0" class="px-4 py-3 text-xs text-slate-400 text-center">
                Aucun résultat pour « <span x-text="search"></span> »
            </li>
        </ul>
    </div>
</div>
