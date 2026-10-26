@php
    use App\Enums\LevelExpired;
    use App\Enums\StatusAktif;

    $levelAktif = $filter['level'] ?? null;
    $adaFilter = $jumlahFilterAktif > 0 || filled($filter['q'] ?? null);
@endphp

<x-layouts.app title="Data Domain">
    <x-page-header
        title="Domain"
        description="Pantau masa berlaku dan status seluruh domain milik OPD."
    >
        <x-slot:actions>
            <x-button :href="route('domains.create')" icon="plus">Tambah domain</x-button>
        </x-slot:actions>
    </x-page-header>

    {{-- Pencarian, filter, dan urutan --}}
    <form
        method="GET"
        action="{{ route('domains.index') }}"
        x-data="{ panel: {{ $jumlahFilterAktif > 0 || $errors->any() ? 'true' : 'false' }} }"
        @submit="$dispatch('memuat')"
        class="mb-5 space-y-3"
    >
        @if ($levelAktif)
            <input type="hidden" name="level" value="{{ $levelAktif }}">
        @endif

        <div class="flex flex-wrap items-center gap-2">
            <div class="relative min-w-0 flex-1 basis-60 sm:max-w-sm" role="search">
                <label for="cari-domain" class="sr-only">Cari domain</label>
                <x-lucide-search class="pointer-events-none absolute top-1/2 left-3.5 size-4 -translate-y-1/2 text-ink-subtle" aria-hidden="true" />
                <input
                    id="cari-domain"
                    type="search"
                    name="q"
                    value="{{ $filter['q'] ?? '' }}"
                    placeholder="Nama domain, IP, atau OPD"
                    class="field pl-10"
                >
            </div>

            <x-button variant="secondary">Cari</x-button>

            <button
                type="button"
                @click="panel = ! panel"
                class="focus-ring inline-flex h-11 items-center gap-2 rounded-control border border-line-strong bg-surface px-4 text-sm font-semibold text-ink shadow-xs transition-colors duration-150 hover:bg-surface-muted dark:shadow-none"
                :aria-expanded="panel.toString()"
                aria-controls="panel-filter"
            >
                <x-lucide-filter class="size-4" aria-hidden="true" />
                Filter
                @if ($jumlahFilterAktif > 0)
                    <span class="inline-flex min-w-5 items-center justify-center rounded-full bg-primary px-1.5 text-[11px] font-bold text-on-primary tabular-nums">{{ $jumlahFilterAktif }}<span class="sr-only"> filter aktif</span></span>
                @endif
            </button>

            <div class="ml-auto flex items-center gap-2">
                <label for="urut" class="hidden text-sm text-ink-muted sm:block">Urutkan</label>
                <div class="relative">
                    <select id="urut" name="urut" onchange="this.form.requestSubmit()" class="field h-11 appearance-none py-0 pr-10">
                        @foreach ($opsiUrutan as $kunci => $label)
                            <option value="{{ $kunci }}" @selected(($filter['urut'] ?? 'mendesak') === $kunci)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <x-lucide-chevron-down class="pointer-events-none absolute top-1/2 right-3.5 size-4 -translate-y-1/2 text-ink-subtle" aria-hidden="true" />
                </div>
            </div>
        </div>

        <div
            id="panel-filter"
            x-show="panel"
            x-cloak
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 -translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            class="card p-5"
        >
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <x-select name="opd_id" label="OPD" :options="$opsiOpd" :value="$filter['opd_id'] ?? null" placeholder="Semua OPD" />
                <x-select name="status" label="Status" :options="StatusAktif::opsi()" :value="$filter['status'] ?? null" placeholder="Semua status" />
                <x-input name="expired_dari" label="Expired dari" type="date" :value="$filter['expired_dari'] ?? null" />
                <x-input name="expired_sampai" label="Expired sampai" type="date" :value="$filter['expired_sampai'] ?? null" />
            </div>
            <div class="mt-5 flex flex-wrap items-center justify-end gap-2 border-t border-line pt-4">
                @if ($adaFilter)
                    <x-button :href="route('domains.index')" variant="ghost" icon="x">Reset semua</x-button>
                @endif
                <x-button icon="filter">Terapkan filter</x-button>
            </div>
        </div>
    </form>

    {{-- Pintasan level peringatan --}}
    <nav class="mb-4 flex gap-1.5 overflow-x-auto pb-1" aria-label="Filter level peringatan">
        @foreach ([null => 'Semua', 'critical' => 'Critical', 'warning' => 'Warning', 'normal' => 'Normal'] as $kunci => $label)
            @php $aktif = $levelAktif === ($kunci ?: null); @endphp
            <a
                href="{{ request()->fullUrlWithQuery(['level' => $kunci ?: null, 'page' => null]) }}"
                @if ($aktif) aria-current="true" @endif
                @class([
                    'focus-ring inline-flex shrink-0 items-center gap-2 rounded-full px-3.5 py-1.5 text-sm font-medium transition-colors duration-150',
                    'bg-ink text-canvas' => $aktif,
                    'text-ink-muted hover:bg-surface-muted hover:text-ink' => ! $aktif,
                ])
            >
                @if ($kunci)
                    <span @class([
                        'size-2 rounded-full',
                        'bg-red-600 dark:bg-red-400' => $kunci === 'critical',
                        'bg-amber-500 dark:bg-amber-400' => $kunci === 'warning',
                        'bg-emerald-600 dark:bg-emerald-400' => $kunci === 'normal',
                    ]) aria-hidden="true"></span>
                @endif
                {{ $label }}
            </a>
        @endforeach
    </nav>

    @if ($daftarDomain->isEmpty())
        <div class="card">
            @if ($adaFilter || $levelAktif)
                <x-empty-state
                    icon="search-x"
                    title="Tidak ada domain yang cocok"
                    description="Coba ubah kata kunci, longgarkan rentang tanggal, atau pilih OPD dan status lain."
                >
                    <x-button :href="route('domains.index')" variant="secondary" icon="x">Reset filter</x-button>
                </x-empty-state>
            @else
                <x-empty-state
                    icon="globe"
                    title="Catat domain pertama"
                    description="Tambahkan domain milik OPD agar masa berlakunya bisa dipantau."
                >
                    <x-button :href="route('domains.create')" icon="plus">Tambah domain</x-button>
                </x-empty-state>
            @endif
        </div>
    @else
        <x-table caption="Daftar domain" :skeleton-cols="5">
            <x-slot:head>
                <th scope="col" class="px-5 py-3.5">Domain</th>
                <th scope="col" class="hidden px-5 py-3.5 lg:table-cell">OPD</th>
                <th scope="col" class="hidden px-5 py-3.5 md:table-cell">Tanggal expired</th>
                <th scope="col" class="px-5 py-3.5">Sisa waktu</th>
                <th scope="col" class="w-px px-3 py-3.5 text-right sm:px-5"><span class="sr-only">Aksi</span></th>
            </x-slot:head>

            @foreach ($daftarDomain as $domain)
                <tr class="transition-colors duration-150 hover:bg-surface-muted/50">
                    <td class="px-5 py-4">
                        <a href="{{ route('domains.show', $domain) }}" class="focus-ring rounded font-semibold break-all text-ink hover:text-primary">
                            {{ $domain->nama_domain }}
                        </a>
                        <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-ink-muted">
                            <span class="inline-flex items-center gap-1.5">
                                <span @class([
                                    'size-1.5 rounded-full',
                                    'bg-emerald-600 dark:bg-emerald-400' => $domain->status === StatusAktif::Aktif,
                                    'bg-ink-subtle' => $domain->status !== StatusAktif::Aktif,
                                ]) aria-hidden="true"></span>
                                {{ $domain->status->label() }}
                            </span>
                            <span class="lg:hidden">{{ $domain->opd->nama_opd }}</span>
                            <span class="tabular-nums md:hidden">Exp. {{ $domain->tanggal_expired->translatedFormat('d M Y') }}</span>
                        </div>
                    </td>
                    <td class="hidden max-w-64 px-5 py-4 text-ink-muted lg:table-cell">
                        <span class="line-clamp-2">{{ $domain->opd->nama_opd }}</span>
                    </td>
                    <td class="hidden px-5 py-4 whitespace-nowrap text-ink-muted tabular-nums md:table-cell">
                        {{ $domain->tanggal_expired->translatedFormat('d M Y') }}
                    </td>
                    <td class="px-5 py-4">
                        <x-badge-status :level="$domain->level_expired">{{ $domain->label_expired }}</x-badge-status>
                    </td>
                    <td class="px-3 py-4 sm:px-5">
                        <div class="flex items-center justify-end gap-1">
                            <a href="{{ route('domains.show', $domain) }}" class="focus-ring hidden size-9 items-center justify-center rounded-lg text-ink-muted transition-colors duration-150 hover:bg-surface-muted hover:text-ink sm:inline-flex" aria-label="Detail {{ $domain->nama_domain }}" title="Detail">
                                <x-lucide-eye class="size-4" aria-hidden="true" />
                            </a>
                            <a href="{{ route('domains.edit', $domain) }}" class="focus-ring inline-flex size-9 items-center justify-center rounded-lg pointer-coarse:size-11 text-ink-muted transition-colors duration-150 hover:bg-surface-muted hover:text-ink" aria-label="Edit {{ $domain->nama_domain }}" title="Edit">
                                <x-lucide-pencil class="size-4" aria-hidden="true" />
                            </a>
                            <x-delete-button
                                :action="route('domains.destroy', $domain)"
                                :label="'Hapus '.$domain->nama_domain"
                                title="Hapus domain?"
                                :message="'Domain '.$domain->nama_domain.' akan dihapus permanen dari daftar pemantauan.'"
                            />
                        </div>
                    </td>
                </tr>
            @endforeach

            @if ($daftarDomain->hasPages())
                <x-slot:footer>
                    {{ $daftarDomain->links() }}
                </x-slot:footer>
            @endif
        </x-table>
    @endif
</x-layouts.app>
