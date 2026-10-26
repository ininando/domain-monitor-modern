@php
    $maksDomain = max(1, $perOpd->max('domains_count'));
    $berisi = $perOpd->where('domains_count', '>', 0)->values();
@endphp

<x-layouts.app title="Pemetaan Domain">
    <x-page-header
        title="Pemetaan domain"
        description="Sebaran kepemilikan domain di setiap Organisasi Perangkat Daerah."
    >
        <x-slot:actions>
            <x-button :href="route('pemetaan.export')" variant="secondary" icon="file-spreadsheet">Export Excel</x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="grid gap-4 sm:grid-cols-3">
        <x-stat-card label="Jumlah OPD" :value="$perOpd->count()" icon="building-2" />
        <x-stat-card label="Total domain" :value="$totalDomain" icon="globe" tone="normal" />
        <x-stat-card label="OPD tanpa domain" :value="$opdTanpaDomain" icon="layers" tone="neutral" hint="Belum memiliki domain tercatat" />
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-2">
        <x-chart
            id="chart-pemetaan-bar"
            title="Jumlah domain per OPD"
            description="Diurutkan dari yang terbanyak."
            type="bar"
            :labels="$berisi->pluck('nama_opd')->all()"
            :values="$berisi->pluck('domains_count')->all()"
            series-label="Jumlah domain"
            height="h-80"
            filename="pemetaan-domain-per-opd.png"
        />

        <x-chart
            id="chart-pemetaan-donut"
            title="Proporsi kepemilikan domain"
            description="{{ \App\Services\StatistikDomain::MAKS_SLICE_DONUT }} OPD terbesar, sisanya digabung."
            type="doughnut"
            :labels="$donut->pluck('label')->all()"
            :values="$donut->pluck('nilai')->all()"
            :colors="$donut->pluck('warna')->all()"
            series-label="Jumlah domain"
            height="h-64"
            filename="proporsi-domain-opd.png"
        >
            <x-slot:legend>
                <ul class="space-y-2.5" role="list" aria-label="Legenda proporsi">
                    @foreach ($donut as $slice)
                        <li class="flex items-center gap-3 text-sm">
                            <span class="size-3 shrink-0 rounded-[4px]" style="background: var({{ $slice['warna'] === 'other' ? '--chart-other' : $slice['warna'] }})" aria-hidden="true"></span>
                            <span class="min-w-0 flex-1 truncate text-ink" title="{{ $slice['label'] }}">{{ $slice['label'] }}</span>
                            <span class="shrink-0 font-semibold text-ink tabular-nums">{{ $slice['nilai'] }}</span>
                            <span class="w-10 shrink-0 text-right text-xs text-ink-muted tabular-nums">{{ $totalDomain ? round($slice['nilai'] / $totalDomain * 100) : 0 }}%</span>
                        </li>
                    @endforeach
                </ul>
            </x-slot:legend>
        </x-chart>
    </div>

    <div class="mt-8 mb-4 flex items-end justify-between gap-4">
        <div>
            <h2 class="text-lg font-semibold text-ink">Tabel pemetaan</h2>
            <p class="mt-0.5 text-sm text-ink-muted">Klik "Lihat detail" untuk melihat daftar domain milik OPD.</p>
        </div>
    </div>

    <x-table caption="Pemetaan jumlah domain per OPD" :skeleton-cols="4">
        <x-slot:head>
            <th scope="col" class="px-5 py-3.5">Nama OPD</th>
            <th scope="col" class="px-5 py-3.5">Jumlah domain</th>
            <th scope="col" class="hidden px-5 py-3.5 md:table-cell">Kritis</th>
            <th scope="col" class="w-px px-5 py-3.5 text-right"><span class="sr-only">Aksi</span></th>
        </x-slot:head>

        @foreach ($perOpd as $opd)
            <tr class="transition-colors duration-150 hover:bg-surface-muted/50">
                <td class="px-5 py-4 font-semibold text-ink">{{ $opd->nama_opd }}</td>
                <td class="px-5 py-4">
                    <div class="flex items-center gap-3">
                        <span class="w-6 font-semibold text-ink tabular-nums">{{ $opd->domains_count }}</span>
                        <div class="hidden h-1.5 w-32 overflow-hidden rounded-full bg-surface-muted sm:block" aria-hidden="true">
                            <div class="h-full rounded-full bg-primary" style="width: {{ round($opd->domains_count / $maksDomain * 100) }}%"></div>
                        </div>
                    </div>
                </td>
                <td class="hidden px-5 py-4 md:table-cell">
                    @if ($opd->domains_kritis_count > 0)
                        <x-badge-status level="critical">{{ $opd->domains_kritis_count }} kritis</x-badge-status>
                    @else
                        <span class="text-sm text-ink-subtle">—</span>
                    @endif
                </td>
                <td class="px-5 py-4 text-right">
                    <a href="{{ route('pemetaan.show', $opd) }}" class="focus-ring inline-flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-sm font-semibold whitespace-nowrap text-primary transition-colors duration-150 hover:bg-primary-soft" aria-label="Lihat detail domain {{ $opd->nama_opd }}">
                        Lihat detail
                        <x-lucide-arrow-right class="size-4" aria-hidden="true" />
                    </a>
                </td>
            </tr>
        @endforeach
    </x-table>
</x-layouts.app>
