@props([
    'variant' => 'neutral', // success, warning, danger, neutral, primary, info
    'size' => 'md',        // sm, md
    'dot' => false,
])

@php
    $variantClass = match($variant) {
        'success' => 'badge-success',
        'warning' => 'badge-warning',
        'danger' => 'badge-danger',
        'primary' => 'badge-primary',
        'info' => 'badge-info',
        default => 'badge-neutral',
    };

    $dotClass = match($variant) {
        'success' => 'bg-emerald-500',
        'warning' => 'bg-amber-500',
        'danger' => 'bg-rose-500',
        'primary' => 'bg-red-600',
        'info' => 'bg-sky-500',
        default => 'bg-slate-400',
    };

    $sizeClass = $size === 'sm' ? 'text-[10px] px-1.5 py-0.5' : 'text-[11px] px-2 py-0.5';
@endphp

<span {{ $attributes->merge(['class' => "admin-badge {$variantClass} {$sizeClass} font-semibold"]) }}>
    @if($dot)
        <span class="w-1.5 h-1.5 rounded-full {{ $dotClass }} shrink-0"></span>
    @endif
    {{ $slot }}
</span>
