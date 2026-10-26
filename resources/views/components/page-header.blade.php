@props([
    'title',
    'description' => null,
    'back' => null,
])

<div {{ $attributes->class('mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between') }}>
    <div class="min-w-0">
        @if ($back)
            <a href="{{ $back }}" class="focus-ring mb-3 inline-flex items-center gap-1.5 rounded-md text-sm font-medium text-ink-muted hover:text-ink">
                <x-lucide-arrow-left class="size-4" aria-hidden="true" />
                Kembali
            </a>
        @endif
        <h1 class="text-2xl font-bold tracking-tight text-balance text-ink sm:text-[28px] sm:leading-9">{{ $title }}</h1>
        @if ($description)
            <p class="mt-1.5 text-sm text-ink-muted">{{ $description }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="flex shrink-0 flex-wrap items-center gap-2">
            {{ $actions }}
        </div>
    @endisset
</div>
