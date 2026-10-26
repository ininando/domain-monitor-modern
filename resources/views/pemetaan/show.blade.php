@php
    use App\Enums\LevelExpired;
@endphp

<x-layouts.app :title="'Pemetaan · '.$opd->nama_opd">
    <x-page-header
        :title="$opd->nama_opd"
        :description="$domains->count().' domain tercatat untuk OPD ini.'"
        :back="route('pemetaan.index')"
    >
        <x-slot:actions>
            <x-button :href="route('domains.create', ['opd_id' => $opd->id])" icon="plus">Tambah domain</x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="grid gap-6 lg:grid-cols-3">
        <section class="card p-6" aria-labelledby="judul-profil">
            <h2 id="judul-profil" class="text-base font-semibold text-ink">Profil OPD</h2>
            <dl class="mt-5 space-y-4 text-sm">
                @foreach ([
                    ['user-round', 'Kepala OPD', $opd->kepala_opd],
                    ['phone', 'Kontak', $opd->kontak],
                    ['map-pin', 'Alamat', $opd->alamat],
                ] as [$ikon, $label, $nilai])
                    <div>
                        <dt class="flex items-center gap-2 text-xs font-medium text-ink-muted">
                            <x-dynamic-component :component="'lucide-'.$ikon" class="size-4 shrink-0 text-ink-subtle" aria-hidden="true" />
                            {{ $label }}
                        </dt>
                        <dd class="mt-0.5 pl-6 font-medium text-ink">{{ $nilai ?: '—' }}</dd>
                    </div>
                @endforeach
            </dl>

            <div class="mt-6 border-t border-line pt-5">
                <h3 class="text-xs font-semibold tracking-wide text-ink-muted uppercase">Status masa berlaku</h3>
                <ul class="mt-3 space-y-2" role="list">
                    @foreach ([LevelExpired::Critical, LevelExpired::Warning, LevelExpired::Normal] as $level)
                        <li class="flex items-center justify-between">
                            <x-badge-status :level="$level" />
                            <span class="text-sm font-semibold text-ink tabular-nums">{{ $perLevel[$level->value] }} domain</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <a href="{{ route('opd.edit', $opd) }}" class="focus-ring mt-6 inline-flex items-center gap-1.5 rounded text-sm font-semibold text-primary hover:underline">
                <x-lucide-pencil class="size-4" aria-hidden="true" />
                Edit profil OPD
            </a>
        </section>

        <div class="lg:col-span-2">
            @if ($domains->isEmpty())
                <div class="card">
                    <x-empty-state
                        icon="globe"
                        title="OPD ini belum memiliki domain"
                        description="Tambahkan domain agar masa berlakunya ikut dipantau."
                    >
                        <x-button :href="route('domains.create', ['opd_id' => $opd->id])" icon="plus">Tambah domain</x-button>
                    </x-empty-state>
                </div>
            @else
                <x-table :caption="'Domain milik '.$opd->nama_opd" :skeleton-cols="3">
                    <x-slot:head>
                        <th scope="col" class="px-5 py-3.5">Domain</th>
                        <th scope="col" class="hidden px-5 py-3.5 sm:table-cell">Tanggal expired</th>
                        <th scope="col" class="px-5 py-3.5">Sisa waktu</th>
                    </x-slot:head>

                    @foreach ($domains as $domain)
                        <tr class="transition-colors duration-150 hover:bg-surface-muted/50">
                            <td class="px-5 py-4">
                                <a href="{{ route('domains.show', $domain) }}" class="focus-ring rounded font-semibold break-all text-ink hover:text-primary">{{ $domain->nama_domain }}</a>
                                <p class="mt-0.5 text-xs text-ink-muted">{{ $domain->status->label() }}</p>
                            </td>
                            <td class="hidden px-5 py-4 whitespace-nowrap text-ink-muted tabular-nums sm:table-cell">{{ $domain->tanggal_expired->translatedFormat('d M Y') }}</td>
                            <td class="px-5 py-4">
                                <x-badge-status :level="$domain->level_expired">{{ $domain->label_expired }}</x-badge-status>
                            </td>
                        </tr>
                    @endforeach
                </x-table>
            @endif
        </div>
    </div>
</x-layouts.app>
