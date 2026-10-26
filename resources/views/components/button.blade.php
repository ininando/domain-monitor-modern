@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'icon' => null,
    'type' => 'submit',
    'loading' => null,
])

{{--
    Tombol serbaguna.
    - variant: primary | secondary | ghost | danger
    - size: sm | md
    - href: jika diisi dirender sebagai <a>
    - icon: nama ikon Lucide (tanpa prefix)
    - loading: nama variabel Alpine (mis. "mengirim") untuk state memuat
--}}

@php
    $classes = [
        'focus-ring inline-flex items-center justify-center gap-2 rounded-control font-semibold whitespace-nowrap transition-colors duration-150 disabled:cursor-not-allowed disabled:opacity-60',
        'h-9 px-3.5 text-sm' => $size === 'sm',
        'h-11 px-4.5 text-sm' => $size === 'md',
        'bg-primary text-on-primary shadow-xs hover:bg-primary-hover' => $variant === 'primary',
        'border border-line-strong bg-surface text-ink shadow-xs hover:bg-surface-muted dark:shadow-none' => $variant === 'secondary',
        'text-ink-muted hover:bg-surface-muted hover:text-ink' => $variant === 'ghost',
        'bg-red-600 text-white shadow-xs hover:bg-red-700 dark:bg-red-500 dark:text-red-950 dark:hover:bg-red-400' => $variant === 'danger',
    ];
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>
        @if ($icon)
            <x-dynamic-component :component="'lucide-'.$icon" class="size-4 shrink-0" aria-hidden="true" />
        @endif
        {{ $slot }}
    </a>
@else
    <button
        type="{{ $type }}"
        @if ($loading) x-bind:disabled="{{ $loading }}" x-bind:aria-busy="{{ $loading }}.toString()" @endif
        {{ $attributes->class($classes) }}
    >
        @if ($loading)
            <x-lucide-loader-circle x-show="{{ $loading }}" x-cloak class="size-4 shrink-0 animate-spin" aria-hidden="true" />
        @endif
        @if ($icon && $loading)
            <x-dynamic-component :component="'lucide-'.$icon" class="size-4 shrink-0" aria-hidden="true" x-show="! {{ $loading }}" />
        @elseif ($icon)
            <x-dynamic-component :component="'lucide-'.$icon" class="size-4 shrink-0" aria-hidden="true" />
        @endif
        {{ $slot }}
    </button>
@endif
