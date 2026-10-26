@php
    $jenisAwal = old('jenis', $filter['jenis'] ?? 'opd');
    $ikonJenis = ['opd' => 'building-2', 'status' => 'circle-check-big', 'expired' => 'calendar-clock'];
    $deskripsiJenis = [
        'opd' => 'Semua domain milik satu OPD.',
        'status' => 'Domain aktif atau tidak aktif.',
        'expired' => 'Domain yang sudah lewat masa berlaku.',
    ];
    $parameter = request()->only(['jenis', 'opd_id', 'status', 'dari', 'sampai', 'sertakan_akan_expired']);
@endphp

<x-layouts.app title="Laporan">
    <x-page-header
        title="Laporan"
        description="Susun laporan domain, tinjau isinya, lalu unduh sebagai PDF atau Excel."
    />

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-[22rem_minmax(0,1fr)] lg:items-start">
        {{-- Parameter laporan --}}
        <form
            method="GET"
            action="{{ route('laporan.index') }}"
            x-data="{ jenis: @js($jenisAwal) }"
            @submit="$dispatch('memuat')"
            class="card p-6 lg:sticky lg:top-24"
            novalidate
        >
            <fieldset>
                <legend class="text-sm font-semibold text-ink">Jenis laporan</legend>
                <div class="mt-3 space-y-2">
                    @foreach ($opsiJenis as $kunci => $label)
                        <label class="group flex cursor-pointer items-start gap-3 rounded-xl border border-line p-3.5 transition-colors duration-150 hover:border-line-strong has-[:checked]:border-primary has-[:checked]:bg-primary-soft/60 has-[:focus-visible]:ring-3 has-[:focus-visible]:ring-primary/30">
                            <input type="radio" name="jenis" value="{{ $kunci }}" x-model="jenis" class="sr-only" @checked($jenisAwal === $kunci)>
                            <span class="inline-flex size-9 shrink-0 items-center justify-center rounded-lg bg-surface-muted text-ink-muted group-has-[:checked]:bg-primary group-has-[:checked]:text-on-primary">
                                <x-dynamic-component :component="'lucide-'.$ikonJenis[$kunci]" class="size-4" aria-hidden="true" />
                            </span>
                            <span class="min-w-0">
                                <span class="block text-sm font-semibold text-ink">{{ $label }}</span>
                                <span class="block text-xs text-ink-muted">{{ $deskripsiJenis[$kunci] }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('jenis')
                    <p class="mt-2 text-xs font-medium text-red-700 dark:text-red-300">{{ $message }}</p>
                @enderror
            </fieldset>

            <div class="mt-6 space-y-5 border-t border-line pt-6">
                <div x-show="jenis === 'opd'" x-cloak>
                    <x-select name="opd_id" label="OPD" :options="$opsiOpd" :value="$filter['opd_id'] ?? null" placeholder="Pilih OPD" required x-bind:disabled="jenis !== 'opd'" />
                </div>

                <div x-show="jenis === 'status'" x-cloak>
                    <x-select name="status" label="Status domain" :options="$opsiStatus" :value="$filter['status'] ?? null" placeholder="Pilih status" required x-bind:disabled="jenis !== 'status'" />
                </div>

                <div x-show="jenis === 'expired'" x-cloak>
                    <label class="flex cursor-pointer items-start gap-2.5 text-sm text-ink select-none">
                        <input type="checkbox" name="sertakan_akan_expired" value="1" x-bind:disabled="jenis !== 'expired'" @checked($filter['sertakan_akan_expired'] ?? false) class="focus-ring mt-0.5 size-4 rounded border-line-strong accent-primary">
                        <span>
                            Sertakan yang akan expired
                            <span class="block text-xs text-ink-muted">Domain yang expired dalam 30 hari ke depan ikut dimasukkan.</span>
                        </span>
                    </label>
                </div>

                <fieldset>
                    <legend class="text-sm font-medium text-ink">Periode tanggal expired <span class="font-normal text-ink-muted">(opsional)</span></legend>
                    <div class="mt-1.5 grid grid-cols-2 gap-3">
                        <x-input name="dari" label="Dari" type="date" :value="$filter['dari'] ?? null" class="[&_label]:text-xs [&_label]:text-ink-muted" />
                        <x-input name="sampai" label="Sampai" type="date" :value="$filter['sampai'] ?? null" class="[&_label]:text-xs [&_label]:text-ink-muted" />
                    </div>
                </fieldset>
            </div>

            <x-button icon="eye" class="mt-6 w-full">Tampilkan pratinjau</x-button>
        </form>

        {{-- Pratinjau --}}
        <section class="card min-w-0 overflow-hidden" aria-labelledby="judul-pratinjau" x-data="{ memuat: false }" @memuat.window="memuat = true">
            @if ($pratinjau === null)
                <h2 id="judul-pratinjau" class="sr-only">Pratinjau laporan</h2>
                <x-empty-state
                    icon="file-text"
                    title="Pilih parameter laporan"
                    description="Tentukan jenis laporan dan periodenya, lalu klik Tampilkan pratinjau untuk melihat isinya sebelum diunduh."
                    class="py-24"
                />
            @else
                <div class="flex flex-col gap-4 border-b border-line px-6 py-5 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <p class="text-xs font-semibold tracking-wide text-primary uppercase">Pratinjau</p>
                        <h2 id="judul-pratinjau" class="mt-1 text-lg font-bold text-ink">{{ $pratinjau['judul'] }}</h2>
                        <p class="mt-0.5 text-sm text-ink-muted">{{ $pratinjau['subjudul'] }}</p>
                        @if ($pratinjau['periode'])
                            <p class="mt-1 text-xs text-ink-muted">Periode expired: {{ $pratinjau['periode'] }}</p>
                        @endif
                    </div>
                    <div class="flex shrink-0 gap-2">
                        <x-button :href="route('laporan.excel', $parameter)" variant="secondary" size="sm" icon="file-spreadsheet">Excel</x-button>
                        <x-button :href="route('laporan.pdf', $parameter)" size="sm" icon="download">PDF</x-button>
                    </div>
                </div>

                <div x-show="memuat" x-cloak class="space-y-3 p-6" aria-hidden="true">
                    @for ($i = 0; $i < 6; $i++)
                        <div class="skeleton h-5 w-full"></div>
                    @endfor
                </div>

                <div x-show="! memuat">
                    @if ($pratinjau['domains']->isEmpty())
                        <x-empty-state
                            icon="search-x"
                            title="Tidak ada domain untuk laporan ini"
                            description="Coba ubah periode atau pilih parameter lain."
                        />
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <caption class="sr-only">{{ $pratinjau['judul'] }} — {{ $pratinjau['subjudul'] }}</caption>
                                <thead class="border-b border-line bg-surface-muted/60 text-xs font-semibold tracking-wide text-ink-muted uppercase">
                                    <tr>
                                        <th scope="col" class="w-12 px-5 py-3">No</th>
                                        <th scope="col" class="px-5 py-3">Nama domain</th>
                                        <th scope="col" class="hidden px-5 py-3 md:table-cell">OPD</th>
                                        <th scope="col" class="hidden px-5 py-3 sm:table-cell">Status</th>
                                        <th scope="col" class="px-5 py-3">Tanggal expired</th>
                                        <th scope="col" class="hidden px-5 py-3 xl:table-cell">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-line">
                                    @foreach ($pratinjau['domains'] as $domain)
                                        <tr>
                                            <td class="px-5 py-3 text-ink-muted tabular-nums">{{ $loop->iteration }}</td>
                                            <td class="px-5 py-3">
                                                <span class="font-medium text-ink [overflow-wrap:anywhere]">{{ $domain->nama_domain }}</span>
                                                <span class="block text-xs text-ink-muted sm:hidden">{{ $domain->status->label() }}</span>
                                            </td>
                                            <td class="hidden px-5 py-3 text-ink-muted md:table-cell">{{ $domain->opd->nama_opd }}</td>
                                            <td class="hidden px-5 py-3 whitespace-nowrap text-ink sm:table-cell">{{ $domain->status->label() }}</td>
                                            <td class="px-5 py-3">
                                                <span class="block whitespace-nowrap text-ink tabular-nums">{{ $domain->tanggal_expired->translatedFormat('d M Y') }}</span>
                                                <x-badge-status :level="$domain->level_expired" class="mt-1">{{ $domain->label_expired }}</x-badge-status>
                                            </td>
                                            <td class="hidden max-w-56 px-5 py-3 text-ink-muted xl:table-cell">{{ $domain->keterangan ?: '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    <div class="flex items-center justify-between border-t border-line bg-surface-muted/40 px-6 py-3.5 text-sm">
                        <span class="text-ink-muted">Total domain</span>
                        <span class="font-bold text-ink tabular-nums">{{ $pratinjau['domains']->count() }}</span>
                    </div>
                </div>
            @endif
        </section>
    </div>
</x-layouts.app>
