<x-layouts.app title="Data OPD">
    <x-page-header
        title="Organisasi Perangkat Daerah"
        description="Kelola daftar OPD di lingkungan Pemerintah Kota Tomohon beserta jumlah domain yang dimiliki."
    >
        <x-slot:actions>
            <x-button :href="route('opd.create')" icon="plus">Tambah OPD</x-button>
        </x-slot:actions>
    </x-page-header>

    <form
        method="GET"
        action="{{ route('opd.index') }}"
        role="search"
        class="mb-5 flex items-center gap-2"
        @submit="$dispatch('memuat')"
    >
        <div class="relative min-w-0 flex-1 sm:max-w-sm">
            <label for="cari-opd" class="sr-only">Cari OPD</label>
            <x-lucide-search class="pointer-events-none absolute top-1/2 left-3.5 size-4 -translate-y-1/2 text-ink-subtle" aria-hidden="true" />
            <input
                id="cari-opd"
                type="search"
                name="q"
                value="{{ $kataKunci }}"
                placeholder="Nama OPD, kepala, atau kontak"
                class="field pl-10"
            >
        </div>
        <x-button variant="secondary">Cari</x-button>
        @if ($kataKunci !== '')
            <x-button :href="route('opd.index')" variant="ghost" icon="x">Reset</x-button>
        @endif
    </form>

    @if ($daftarOpd->isEmpty())
        <div class="card">
            @if ($kataKunci !== '')
                <x-empty-state
                    icon="search-x"
                    title="Tidak ada OPD yang cocok"
                    description="Tidak ditemukan OPD untuk kata kunci “{{ $kataKunci }}”. Coba kata kunci lain."
                >
                    <x-button :href="route('opd.index')" variant="secondary">Hapus pencarian</x-button>
                </x-empty-state>
            @else
                <x-empty-state
                    icon="building-2"
                    title="Mulai dengan menambahkan OPD"
                    description="OPD diperlukan sebelum Anda dapat mencatat domain yang dimilikinya."
                >
                    <x-button :href="route('opd.create')" icon="plus">Tambah OPD</x-button>
                </x-empty-state>
            @endif
        </div>
    @else
        <x-table caption="Daftar OPD" :skeleton-cols="5">
            <x-slot:head>
                <th scope="col" class="px-5 py-3.5">Nama OPD</th>
                <th scope="col" class="hidden px-5 py-3.5 md:table-cell">Kepala OPD</th>
                <th scope="col" class="hidden px-5 py-3.5 lg:table-cell">Kontak</th>
                <th scope="col" class="w-px px-3 py-3.5 text-center sm:px-5">Domain</th>
                <th scope="col" class="w-px px-3 py-3.5 text-right sm:px-5"><span class="sr-only">Aksi</span></th>
            </x-slot:head>

            @foreach ($daftarOpd as $opd)
                <tr class="transition-colors duration-150 hover:bg-surface-muted/50">
                    <td class="max-w-md px-5 py-4">
                        <p class="font-semibold text-ink">{{ $opd->nama_opd }}</p>
                        @if ($opd->alamat)
                            <p class="mt-0.5 flex items-start gap-1.5 text-xs text-ink-muted">
                                <x-lucide-map-pin class="mt-px size-3.5 shrink-0" aria-hidden="true" />
                                <span class="line-clamp-1">{{ $opd->alamat }}</span>
                            </p>
                        @endif
                        {{-- Di layar kecil, kepala dan kontak dilipat ke kolom pertama. --}}
                        <dl class="mt-2 space-y-0.5 text-xs text-ink-muted lg:hidden">
                            <div class="md:hidden"><dt class="sr-only">Kepala OPD</dt><dd>{{ $opd->kepala_opd ?? '—' }}</dd></div>
                            @if ($opd->kontak)
                                <div><dt class="sr-only">Kontak</dt><dd class="tabular-nums">{{ $opd->kontak }}</dd></div>
                            @endif
                        </dl>
                    </td>
                    <td class="hidden px-5 py-4 text-ink-muted md:table-cell">{{ $opd->kepala_opd ?? '—' }}</td>
                    <td class="hidden px-5 py-4 whitespace-nowrap text-ink-muted tabular-nums lg:table-cell">{{ $opd->kontak ?? '—' }}</td>
                    <td class="px-3 py-4 text-center sm:px-5">
                        @if ($opd->domains_count > 0)
                            <a
                                href="{{ route('domains.index', ['opd_id' => $opd->id]) }}"
                                class="focus-ring inline-flex min-w-9 items-center justify-center rounded-full bg-primary-soft px-2.5 py-1 text-xs font-semibold text-on-primary-soft tabular-nums transition-colors duration-150 hover:bg-primary hover:text-on-primary"
                                aria-label="Lihat {{ $opd->domains_count }} domain milik {{ $opd->nama_opd }}"
                            >{{ $opd->domains_count }}</a>
                        @else
                            <span class="inline-flex min-w-9 items-center justify-center rounded-full bg-surface-muted px-2.5 py-1 text-xs font-semibold text-ink-muted tabular-nums">0<span class="sr-only"> domain</span></span>
                        @endif
                    </td>
                    <td class="px-3 py-4 sm:px-5">
                        <div class="flex items-center justify-end gap-1">
                            <a
                                href="{{ route('opd.edit', $opd) }}"
                                class="focus-ring inline-flex size-9 items-center justify-center rounded-lg pointer-coarse:size-11 text-ink-muted transition-colors duration-150 hover:bg-surface-muted hover:text-ink"
                                aria-label="Edit {{ $opd->nama_opd }}"
                                title="Edit"
                            >
                                <x-lucide-pencil class="size-4" aria-hidden="true" />
                            </a>
                            <x-delete-button
                                :action="route('opd.destroy', $opd)"
                                :label="'Hapus '.$opd->nama_opd"
                                title="Hapus OPD?"
                                :message="$opd->domains_count > 0
                                    ? 'OPD “'.$opd->nama_opd.'” masih memiliki '.$opd->domains_count.' domain, sehingga tidak dapat dihapus sebelum domainnya dipindahkan atau dihapus.'
                                    : 'OPD “'.$opd->nama_opd.'” akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.'"
                            />
                        </div>
                    </td>
                </tr>
            @endforeach

            @if ($daftarOpd->hasPages())
                <x-slot:footer>
                    {{ $daftarOpd->links() }}
                </x-slot:footer>
            @endif
        </x-table>
    @endif
</x-layouts.app>
