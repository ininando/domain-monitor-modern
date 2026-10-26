@props([
    'label',
    'value',
    'icon' => 'globe',
    'tone' => 'primary',
    'hint' => null,
    'href' => null,
])

{{-- tone: primary | normal | warning | critical | neutral --}}

@php
    $iconTone = match ($tone) {
        'normal' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',
        'warning' => 'bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300',
        'critical' => 'bg-red-50 text-red-700 dark:bg-red-500/15 dark:text-red-300',
        'neutral' => 'bg-surface-muted text-ink-muted',
        default => 'bg-primary-soft text-on-primary-soft',
    };
    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }}
    @if ($href) href="{{ $href }}" @endif
    {{ $attributes->class([
        'card flex items-start gap-4 p-5',
        'focus-ring transition duration-150 hover:-translate-y-0.5 hover:border-line-strong' => $href,
    ]) }}
>
    <div class="min-w-0 flex-1">
        <p class="text-sm font-medium text-ink-muted">{{ $label }}</p>
        <p class="mt-2 text-3xl font-bold tracking-tight text-ink tabular-nums">{{ $value }}</p>
        @if ($hint)
            <p class="mt-1 text-xs text-ink-muted">{{ $hint }}</p>
        @endif
    </div>
    <span class="inline-flex size-11 shrink-0 items-center justify-center rounded-xl {{ $iconTone }}">
        <x-dynamic-component :component="'lucide-'.$icon" class="size-5" aria-hidden="true" />
    </span>
</{{ $tag }}>
