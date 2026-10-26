@php
    $hariIni = today();
@endphp

<x-layouts.app title="Dashboard">
    <x-page-header
        title="Dashboard"
        :description="'Selamat datang, '.auth()->user()->name.'. Data per '.$hariIni->translatedFormat('l, d F Y').'.'"
    >
        <x-slot:actions>
            <x-button :href="route('domains.create')" icon="plus">Tambah domain</x-button>
        </x-slot:actions>
    </x-page-header>

    @if ($ringkasan['sudah_expired'] > 0)
        <div role="alert" class="mb-6 flex flex-col gap-3 rounded-2xl border border-red-200 bg-red-50 p-4 sm:flex-row sm:items-center dark:border-red-500/30 dark:bg-red-500/10">
            <span class="inline-flex size-10 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-300">
                <x-lucide-triangle-alert class="size-5" aria-hidden="true" />
            </span>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold text-red-800 dark:text-red-200">{{ $ringkasan['sudah_expired'] }} domain sudah melewati tanggal expired</p>
                <p class="text-sm text-red-700/90 dark:text-red-200/80">Layanan OPD terkait berisiko tidak dapat diakses. Segera koordinasikan perpanjangan.</p>
            </div>
            <x-button :href="route('domains.index', ['level' => 'critical'])" variant="secondary" size="sm" icon="arrow-right" class="shrink-0">Tinjau</x-button>
        </div>
    @endif

    {{-- Kartu statistik --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat-card
            label="Total domain"
            :value="$ringkasan['total']"
            icon="globe"
            hint="Seluruh domain yang dipantau"
            :href="route('domains.index')"
        />
        <x-stat-card
            label="Domain aktif"
            :value="$ringkasan['aktif']"
            icon="circle-check-big"
            tone="normal"
            :hint="$ringkasan['total'] ? round($ringkasan['aktif'] / $ringkasan['total'] * 100).'% dari total' : null"
            :href="route('domains.index', ['status' => 'aktif'])"
        />
        <x-stat-card
            label="Tidak aktif"
            :value="$ringkasan['tidak_aktif']"
            icon="circle-pause"
            tone="neutral"
            hint="Dinonaktifkan sementara"
            :href="route('domains.index', ['status' => 'tidak_aktif'])"
        />
        <x-stat-card
            label="Akan expired"
            :value="$ringkasan['akan_expired']"
            icon="calendar-clock"
            tone="warning"
            hint="Dalam 30 hari ke depan"
            :href="route('domains.index', ['expired_dari' => $hariIni->toDateString(), 'expired_sampai' => $hariIni->copy()->addDays(30)->toDateString()])"
        />
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-5">
        {{-- Domain yang perlu perhatian --}}
        <section class="card flex flex-col xl:col-span-3" aria-labelledby="judul-perhatian">
            <div class="flex items-start justify-between gap-4 border-b border-line px-6 py-5">
                <div>
                    <h2 id="judul-perhatian" class="text-base font-semibold text-ink">Perlu perhatian</h2>
                    <p class="mt-1 text-sm text-ink-muted">Sudah expired atau expired dalam 30 hari, paling mendesak di atas.</p>
                </div>
                <a href="{{ route('domains.index', ['expired_sampai' => $hariIni->copy()->addDays(30)->toDateString()]) }}" class="focus-ring inline-flex shrink-0 items-center gap-1 rounded text-sm font-semibold text-primary hover:underline">
                    Lihat semua
                    <x-lucide-arrow-right class="size-4" aria-hidden="true" />
                </a>
            </div>

            @if ($perluPerhatian->isEmpty())
                <x-empty-state
                    icon="circle-check-big"
                    title="Semua domain aman"
                    description="Tidak ada domain yang expired dalam 30 hari ke depan."
                    class="flex-1"
                />
            @else
                <ul class="divide-y divide-line" role="list">
                    @foreach ($perluPerhatian as $domain)
                        <li>
                            <a href="{{ route('domains.show', $domain) }}" class="focus-ring flex items-center gap-4 px-6 py-3.5 transition-colors duration-150 hover:bg-surface-muted/60">
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold text-ink">{{ $domain->nama_domain }}</p>
                                    <p class="truncate text-xs text-ink-muted">{{ $domain->opd->nama_opd }}</p>
                                </div>
                                <p class="hidden shrink-0 text-xs text-ink-muted tabular-nums sm:block">{{ $domain->tanggal_expired->translatedFormat('d M Y') }}</p>
                                <x-badge-status :level="$domain->level_expired" class="shrink-0">{{ $domain->label_expired }}</x-badge-status>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        {{-- Bar chart domain per OPD --}}
        <x-chart
            id="chart-domain-per-opd"
            title="Domain per OPD"
            description="Jumlah domain yang dimiliki setiap OPD."
            type="bar"
            :labels="$perOpd->pluck('nama_opd')->all()"
            :values="$perOpd->pluck('domains_count')->all()"
            series-label="Jumlah domain"
            height="h-80"
            filename="domain-per-opd.png"
            class="xl:col-span-2"
        />
    </div>

    {{-- Line chart pertumbuhan --}}
    <x-chart
        id="chart-pertumbuhan"
        title="Pertumbuhan domain"
        description="Total domain terdaftar per bulan dalam 12 bulan terakhir (berdasarkan tanggal registrasi)."
        type="line"
        :labels="$pertumbuhan['labels']"
        :values="$pertumbuhan['total']"
        series-label="Total domain"
        height="h-72"
        filename="pertumbuhan-domain.png"
        class="mt-6"
    >
        <x-slot:table>
            <table class="w-full text-left text-sm">
                <caption class="sr-only">Data pertumbuhan domain per bulan</caption>
                <thead class="text-xs text-ink-muted">
                    <tr>
                        <th scope="col" class="py-1.5 pr-4 font-semibold">Bulan</th>
                        <th scope="col" class="py-1.5 pr-4 text-right font-semibold">Domain baru</th>
                        <th scope="col" class="py-1.5 text-right font-semibold">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @foreach ($pertumbuhan['labels'] as $i => $bulan)
                        <tr>
                            <td class="py-1.5 pr-4 text-ink">{{ $bulan }}</td>
                            <td class="py-1.5 pr-4 text-right text-ink tabular-nums">{{ $pertumbuhan['baru'][$i] }}</td>
                            <td class="py-1.5 text-right font-medium text-ink tabular-nums">{{ $pertumbuhan['total'][$i] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </x-slot:table>
    </x-chart>
</x-layouts.app>
