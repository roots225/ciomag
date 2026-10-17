@props([
    'name' => '',
    'value' => '1',
    'checked' => false,
    'label' => '',
    'description' => null,
    'badge' => null,
])

<label
    x-data="{ isChecked: @js((bool) $checked) }"
    {{ $attributes->merge(['class' => 'group inline-flex items-start gap-3 cursor-pointer select-none']) }}
>
    <div class="relative flex items-center justify-center mt-0.5 shrink-0">
        <input
            type="checkbox"
            name="{{ $name }}"
            value="{{ $value }}"
            x-model="isChecked"
            @checked($checked)
            class="peer sr-only"
        >
        <div
            class="w-5 h-5 rounded-md border transition-all duration-150 flex items-center justify-center shadow-2xs"
            :class="isChecked
                ? 'bg-red-700 border-red-700 text-white ring-2 ring-red-700/20'
                : 'bg-white border-slate-300 group-hover:border-red-600 text-transparent'"
        >
            <i data-lucide="check" class="w-3.5 h-3.5 stroke-[3]"></i>
        </div>
    </div>

    @if($label || $slot->isNotEmpty())
        <div class="text-xs leading-snug">
            <div class="font-semibold text-slate-800 group-hover:text-slate-950 transition flex items-center gap-1.5">
                <span>{{ $label ?: $slot }}</span>
                @if($badge)
                    <span class="px-1.5 py-0.5 text-[10px] font-bold uppercase bg-slate-100 text-slate-600 rounded-xs">
                        {{ $badge }}
                    </span>
                @endif
            </div>
            @if($description)
                <p class="text-[11px] text-slate-500 mt-0.5">{{ $description }}</p>
            @endif
        </div>
    @endif
</label>
