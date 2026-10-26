{{--
    Tampilan pagination kustom. Didaftarkan sebagai default di AppServiceProvider,
    jadi cukup panggil {{ $paginator->links() }}.
--}}

@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi halaman" class="flex flex-col items-center justify-between gap-3 sm:flex-row">
        <p class="text-sm text-ink-muted">
            Menampilkan
            <span class="font-semibold text-ink tabular-nums">{{ $paginator->firstItem() }}</span>–<span class="font-semibold text-ink tabular-nums">{{ $paginator->lastItem() }}</span>
            dari
            <span class="font-semibold text-ink tabular-nums">{{ $paginator->total() }}</span>
            data
        </p>

        @php
            $itemClass = 'focus-ring inline-flex h-9 min-w-9 items-center justify-center rounded-lg px-2.5 text-sm font-medium tabular-nums transition-colors duration-150';
        @endphp

        <ul class="flex items-center gap-1" role="list">
            <li>
                @if ($paginator->onFirstPage())
                    <span class="{{ $itemClass }} cursor-not-allowed text-ink-subtle/60" role="link" aria-disabled="true" aria-label="Halaman sebelumnya">
                        <x-lucide-chevron-left class="size-4" aria-hidden="true" />
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="{{ $itemClass }} text-ink-muted hover:bg-surface-muted hover:text-ink" aria-label="Halaman sebelumnya">
                        <x-lucide-chevron-left class="size-4" aria-hidden="true" />
                    </a>
                @endif
            </li>

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li><span class="{{ $itemClass }} text-ink-subtle" aria-hidden="true">{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        <li class="hidden sm:block">
                            @if ($page == $paginator->currentPage())
                                <span class="{{ $itemClass }} bg-primary text-on-primary" aria-current="page">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="{{ $itemClass }} text-ink-muted hover:bg-surface-muted hover:text-ink" aria-label="Halaman {{ $page }}">{{ $page }}</a>
                            @endif
                        </li>
                    @endforeach
                @endif
            @endforeach

            <li class="sm:hidden">
                <span class="px-2 text-sm text-ink-muted tabular-nums">{{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span>
            </li>

            <li>
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="{{ $itemClass }} text-ink-muted hover:bg-surface-muted hover:text-ink" aria-label="Halaman berikutnya">
                        <x-lucide-chevron-right class="size-4" aria-hidden="true" />
                    </a>
                @else
                    <span class="{{ $itemClass }} cursor-not-allowed text-ink-subtle/60" role="link" aria-disabled="true" aria-label="Halaman berikutnya">
                        <x-lucide-chevron-right class="size-4" aria-hidden="true" />
                    </span>
                @endif
            </li>
        </ul>
    </nav>
@endif
