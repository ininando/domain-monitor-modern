@props([
    'level' => 'neutral',
])

{{--
    Badge status = titik warna + teks (tidak hanya mengandalkan warna).
    level: App\Enums\LevelExpired | normal | warning | critical | info | neutral
    Teks diisi lewat slot; jika kosong dan level berupa enum, dipakai label level.
--}}

@php
    $enum = $level instanceof \App\Enums\LevelExpired ? $level : null;
    $key = $enum?->value ?? $level;

    $tone = match ($key) {
        'normal' => ['bg-emerald-50 text-emerald-700 ring-emerald-600/15 dark:bg-emerald-500/15 dark:text-emerald-300 dark:ring-emerald-400/20', 'bg-emerald-600 dark:bg-emerald-400'],
        'warning' => ['bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-500/15 dark:text-amber-300 dark:ring-amber-400/20', 'bg-amber-500 dark:bg-amber-400'],
        'critical' => ['bg-red-50 text-red-700 ring-red-600/15 dark:bg-red-500/15 dark:text-red-300 dark:ring-red-400/20', 'bg-red-600 dark:bg-red-400'],
        'info' => ['bg-primary-soft text-on-primary-soft ring-primary/20', 'bg-primary'],
        default => ['bg-surface-muted text-ink-muted ring-line-strong/60', 'bg-ink-subtle'],
    };
@endphp

<span {{ $attributes->class(['inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold whitespace-nowrap ring-1 ring-inset', $tone[0]]) }}>
    <span class="size-1.5 shrink-0 rounded-full {{ $tone[1] }}" aria-hidden="true"></span>
    @if ($slot->isEmpty() && $enum)
        {{ $enum->label() }}
    @else
        {{ $slot }}
    @endif
</span>
