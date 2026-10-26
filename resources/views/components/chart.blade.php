@props([
    'id',
    'title',
    'description' => null,
    'type' => 'bar',
    'labels' => [],
    'values' => [],
    'colors' => null,
    'seriesLabel' => 'Domain',
    'height' => 'h-72',
    'filename' => null,
    'exportable' => true,
])

{{--
    Kartu chart (Chart.js) dengan skeleton, tombol unduh PNG, dan tabel data sebagai alternatif aksesibel.
    Slot `legend` opsional (mis. legenda donut), slot `table` opsional untuk tabel data kustom.
--}}

@php
    $opsi = [
        'type' => $type,
        'labels' => array_values($labels),
        'values' => array_values($values),
        'colors' => $colors,
        'seriesLabel' => $seriesLabel,
        'filename' => $filename ?? $id.'-'.now()->format('Ymd').'.png',
    ];
@endphp

<section {{ $attributes->class('card flex min-w-0 flex-col p-6') }} aria-labelledby="{{ $id }}-judul" x-data="chart(@js($opsi))">
    <div class="mb-5 flex items-start justify-between gap-4">
        <div class="min-w-0">
            <h2 id="{{ $id }}-judul" class="text-base font-semibold text-ink">{{ $title }}</h2>
            @if ($description)
                <p class="mt-1 text-sm text-ink-muted">{{ $description }}</p>
            @endif
        </div>
        @if ($exportable)
            <button
                type="button"
                @click="unduhPng()"
                :disabled="! siap"
                class="focus-ring inline-flex h-9 shrink-0 items-center gap-1.5 rounded-lg border border-line-strong px-3 text-xs font-semibold text-ink-muted transition-colors duration-150 hover:bg-surface-muted hover:text-ink disabled:opacity-50"
                aria-label="Unduh chart {{ $title }} sebagai PNG"
            >
                <x-lucide-download class="size-3.5" aria-hidden="true" />
                PNG
            </button>
        @endif
    </div>

    <div class="flex flex-1 flex-col gap-5 {{ isset($legend) ? 'sm:flex-row sm:items-center' : '' }}">
        <div class="relative min-w-0 {{ $height }} {{ isset($legend) ? 'sm:w-1/2' : 'w-full' }}">
            <div x-show="! siap" class="skeleton absolute inset-0 rounded-xl" aria-hidden="true"></div>
            <canvas x-ref="canvas" role="img" aria-label="{{ $title }}. Rincian angka tersedia pada tabel data di bawah chart."></canvas>
        </div>

        @isset($legend)
            <div class="min-w-0 flex-1">{{ $legend }}</div>
        @endisset
    </div>

    <details class="group mt-5 border-t border-line pt-4">
        <summary class="focus-ring inline-flex cursor-pointer list-none items-center gap-1.5 rounded text-xs font-semibold text-ink-muted hover:text-ink [&::-webkit-details-marker]:hidden">
            <x-lucide-chevron-right class="size-3.5 transition-transform duration-150 group-open:rotate-90" aria-hidden="true" />
            Lihat data tabel
        </summary>
        <div class="mt-3 max-h-64 overflow-auto">
            @isset($table)
                {{ $table }}
            @else
                <table class="w-full text-left text-sm">
                    <caption class="sr-only">Data {{ $title }}</caption>
                    <thead class="text-xs text-ink-muted">
                        <tr>
                            <th scope="col" class="py-1.5 pr-4 font-semibold">Kategori</th>
                            <th scope="col" class="py-1.5 text-right font-semibold">{{ $seriesLabel }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @foreach (array_values($labels) as $i => $label)
                            <tr>
                                <td class="py-1.5 pr-4 text-ink">{{ $label }}</td>
                                <td class="py-1.5 text-right font-medium text-ink tabular-nums">{{ number_format(array_values($values)[$i] ?? 0, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endisset
        </div>
    </details>
</section>
