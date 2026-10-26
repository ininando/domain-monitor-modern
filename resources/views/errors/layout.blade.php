{{-- Kerangka halaman error. Variabel: $kode, $judul, $pesan, $ikon, $tone --}}

@php
    $tone = $tone ?? 'primary';
    $ikonTone = match ($tone) {
        'critical' => 'bg-red-50 text-red-700 ring-red-600/10 dark:bg-red-500/15 dark:text-red-300',
        'warning' => 'bg-amber-50 text-amber-700 ring-amber-600/10 dark:bg-amber-500/15 dark:text-amber-300',
        default => 'bg-primary-soft text-on-primary-soft ring-primary/10',
    };
@endphp

<x-layouts.guest :title="$judul">
    <main class="relative flex min-h-dvh items-center justify-center overflow-hidden px-5 py-16">
        <div class="pointer-events-none absolute inset-0 opacity-[0.5] [background-image:radial-gradient(var(--line)_1px,transparent_1px)] [background-size:22px_22px] [mask-image:radial-gradient(ellipse_at_center,black,transparent_70%)]" aria-hidden="true"></div>

        <div class="relative w-full max-w-md text-center">
            <span class="mx-auto inline-flex size-16 items-center justify-center rounded-2xl ring-8 {{ $ikonTone }}">
                <x-dynamic-component :component="'lucide-'.$ikon" class="size-7" aria-hidden="true" />
            </span>

            <p class="mt-6 text-sm font-semibold tracking-wider text-ink-subtle tabular-nums">ERROR {{ $kode }}</p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-balance text-ink">{{ $judul }}</h1>
            <p class="mt-3 text-base text-pretty text-ink-muted">{{ $pesan }}</p>

            <div class="mt-8 flex flex-col-reverse items-center justify-center gap-2 sm:flex-row">
                <x-button type="button" variant="secondary" icon="arrow-left" onclick="history.length > 1 ? history.back() : location.assign('{{ url('/') }}')">
                    Halaman sebelumnya
                </x-button>
                <x-button :href="url('/')" icon="home">Ke beranda</x-button>
            </div>
        </div>
    </main>
</x-layouts.guest>
