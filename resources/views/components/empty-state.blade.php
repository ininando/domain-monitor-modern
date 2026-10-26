@props([
    'icon' => 'inbox',
    'title',
    'description' => null,
])

<div {{ $attributes->class('flex flex-col items-center justify-center px-6 py-14 text-center') }}>
    <span class="inline-flex size-14 items-center justify-center rounded-2xl bg-surface-muted text-ink-muted">
        <x-dynamic-component :component="'lucide-'.$icon" class="size-6" aria-hidden="true" />
    </span>
    <h3 class="mt-4 text-base font-semibold text-ink">{{ $title }}</h3>
    @if ($description)
        <p class="mt-1 max-w-sm text-sm text-ink-muted">{{ $description }}</p>
    @endif
    @if ($slot->isNotEmpty())
        <div class="mt-5 flex flex-wrap items-center justify-center gap-2">
            {{ $slot }}
        </div>
    @endif
</div>
