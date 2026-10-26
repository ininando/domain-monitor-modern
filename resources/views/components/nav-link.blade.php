@props([
    'href' => null,
    'active' => false,
    'icon',
])

@php
    $base = 'group relative flex items-center gap-3 rounded-control px-3 py-2.5 text-sm font-medium transition-colors duration-150 lg:sidebar-collapsed:justify-center lg:sidebar-collapsed:px-0';
@endphp

@if ($href)
    <a
        href="{{ $href }}"
        @if ($active) aria-current="page" @endif
        {{ $attributes->class([
            $base,
            'focus-ring',
            'bg-primary-soft text-on-primary-soft' => $active,
            'text-ink-muted hover:bg-surface-muted hover:text-ink' => ! $active,
        ]) }}
    >
        <x-dynamic-component :component="'lucide-'.$icon" class="size-5 shrink-0" aria-hidden="true" />
        <span class="truncate lg:sidebar-collapsed:sr-only">{{ $slot }}</span>
        <span class="pointer-events-none absolute left-full z-50 ml-3 hidden rounded-md bg-slate-900 px-2.5 py-1.5 text-xs font-medium whitespace-nowrap text-white shadow-pop dark:bg-slate-700 lg:sidebar-collapsed:group-hover:block lg:sidebar-collapsed:group-focus-visible:block" aria-hidden="true">{{ $slot }}</span>
    </a>
@else
    {{-- Menu untuk fitur yang belum tersedia di fase ini. --}}
    <span {{ $attributes->class([$base, 'cursor-not-allowed text-ink-subtle/70']) }} aria-disabled="true">
        <x-dynamic-component :component="'lucide-'.$icon" class="size-5 shrink-0" aria-hidden="true" />
        <span class="truncate lg:sidebar-collapsed:sr-only">{{ $slot }}</span>
        <span class="ml-auto rounded-full bg-surface-muted px-2 py-0.5 text-[10px] font-semibold text-ink-muted lg:sidebar-collapsed:hidden">Segera</span>
    </span>
@endif
