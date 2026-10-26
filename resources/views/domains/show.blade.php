@php
    use App\Enums\LevelExpired;
    use App\Enums\StatusAktif;

    $level = $domain->level_expired;
    $sisa = $domain->sisa_hari;

    $warnaLevel = match ($level) {
        LevelExpired::Critical => ['ring' => 'border-red-200 dark:border-red-500/30', 'bar' => 'bg-red-600 dark:bg-red-400', 'angka' => 'text-red-700 dark:text-red-300', 'latar' => 'bg-red-50/60 dark:bg-red-500/5'],
        LevelExpired::Warning => ['ring' => 'border-amber-200 dark:border-amber-500/30', 'bar' => 'bg-amber-500 dark:bg-amber-400', 'angka' => 'text-amber-700 dark:text-amber-300', 'latar' => 'bg-amber-50/60 dark:bg-amber-500/5'],
        LevelExpired::Normal => ['ring' => 'border-emerald-200 dark:border-emerald-500/30', 'bar' => 'bg-emerald-600 dark:bg-emerald-400', 'angka' => 'text-emerald-700 dark:text-emerald-300', 'latar' => 'bg-emerald-50/60 dark:bg-emerald-500/5'],
    };

    $pesanLevel = match (true) {
        $sisa < 0 => 'Domain sudah melewati tanggal expired. Segera lakukan perpanjangan agar layanan tidak terhenti.',
        $level === LevelExpired::Critical => 'Masa berlaku hampir habis. Prioritaskan perpanjangan minggu ini.',
        $level === LevelExpired::Warning => 'Masa berlaku akan habis dalam sebulan. Jadwalkan perpanjangan.',
        default => 'Masa berlaku masih aman.',
    };

    $nameservers = collect(preg_split('/\r\n|\r|\n/', (string) $domain->nameserver))->map(fn ($ns) => trim($ns))->filter();
@endphp

<x-layouts.app :title="$domain->nama_domain">
    <x-page-header :title="$domain->nama_domain" :back="route('domains.index')" class="[&_h1]:break-all">
        <x-slot:actions>
            <x-delete-button
                :action="route('domains.destroy', $domain)"
                :label="'Hapus '.$domain->nama_domain"
                title="Hapus domain?"
                :message="'Domain '.$domain->nama_domain.' akan dihapus permanen dari daftar pemantauan.'"
                class="size-11 border border-line-strong bg-surface"
            />
            <x-button :href="route('domains.edit', $domain)" variant="secondary" icon="pencil">Edit</x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="-mt-5 mb-8 flex flex-wrap items-center gap-2">
        <x-badge-status :level="$level" />
        <x-badge-status :level="$domain->status === StatusAktif::Aktif ? 'info' : 'neutral'">Domain {{ $domain->status->label() }}</x-badge-status>
        <span class="text-sm text-ink-muted">milik <span class="font-medium text-ink">{{ $domain->opd->nama_opd }}</span></span>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Countdown --}}
        <section class="card {{ $warnaLevel['ring'] }} {{ $warnaLevel['latar'] }} p-6 lg:col-span-1" aria-labelledby="judul-countdown">
            <h2 id="judul-countdown" class="flex items-center gap-2 text-sm font-semibold text-ink-muted">
                <x-lucide-calendar-clock class="size-4" aria-hidden="true" />
                Sisa masa berlaku
            </h2>

            <p class="mt-4 flex items-baseline gap-2">
                <span class="text-5xl font-bold tracking-tight tabular-nums {{ $warnaLevel['angka'] }}">{{ abs($sisa) }}</span>
                <span class="text-base font-medium text-ink-muted">{{ $sisa < 0 ? 'hari lalu' : 'hari' }}</span>
            </p>
            <p class="mt-1 text-sm font-semibold text-ink">{{ $domain->label_expired }}</p>
            <p class="mt-3 text-sm text-ink-muted">{{ $pesanLevel }}</p>

            @if ($domain->persen_masa_berlaku !== null)
                <div class="mt-6">
                    <div class="flex justify-between text-xs text-ink-muted">
                        <span>Masa berlaku terpakai</span>
                        <span class="font-semibold text-ink tabular-nums">{{ $domain->persen_masa_berlaku }}%</span>
                    </div>
                    <div class="mt-2 h-2 overflow-hidden rounded-full bg-surface-muted" role="progressbar" aria-label="Masa berlaku terpakai" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $domain->persen_masa_berlaku }}">
                        <div class="h-full rounded-full {{ $warnaLevel['bar'] }}" style="width: {{ $domain->persen_masa_berlaku }}%"></div>
                    </div>
                    <div class="mt-2 flex justify-between text-xs text-ink-muted tabular-nums">
                        <span>{{ $domain->tanggal_registrasi->translatedFormat('d M Y') }}</span>
                        <span>{{ $domain->tanggal_expired->translatedFormat('d M Y') }}</span>
                    </div>
                </div>
            @endif
        </section>

        <div class="space-y-6 lg:col-span-2">
            {{-- Informasi umum --}}
            <section class="card" aria-labelledby="judul-umum">
                <h2 id="judul-umum" class="border-b border-line px-6 py-4 text-base font-semibold text-ink">Informasi umum</h2>
                <dl class="grid gap-x-6 gap-y-5 p-6 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-medium text-ink-muted">OPD pemilik</dt>
                        <dd class="mt-1 text-sm font-medium text-ink">
                            <a href="{{ route('domains.index', ['opd_id' => $domain->opd_id]) }}" class="focus-ring rounded hover:text-primary hover:underline">{{ $domain->opd->nama_opd }}</a>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-ink-muted">Status domain</dt>
                        <dd class="mt-1 text-sm font-medium text-ink">{{ $domain->status->label() }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-ink-muted">Tanggal registrasi</dt>
                        <dd class="mt-1 text-sm font-medium text-ink tabular-nums">{{ $domain->tanggal_registrasi?->translatedFormat('l, d F Y') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-ink-muted">Tanggal expired</dt>
                        <dd class="mt-1 text-sm font-medium text-ink tabular-nums">{{ $domain->tanggal_expired->translatedFormat('l, d F Y') }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-medium text-ink-muted">Keterangan</dt>
                        <dd class="mt-1 text-sm whitespace-pre-line text-ink">{{ $domain->keterangan ?: '—' }}</dd>
                    </div>
                </dl>
            </section>

            {{-- Informasi teknis --}}
            <section class="card" aria-labelledby="judul-teknis">
                <h2 id="judul-teknis" class="border-b border-line px-6 py-4 text-base font-semibold text-ink">Informasi teknis</h2>
                <dl class="grid gap-x-6 gap-y-5 p-6 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-medium text-ink-muted">Registrar</dt>
                        <dd class="mt-1 text-sm font-medium text-ink">{{ $domain->registrar ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-ink-muted">Penyedia hosting</dt>
                        <dd class="mt-1 text-sm font-medium text-ink">{{ $domain->hosting_provider ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-ink-muted">Alamat IP</dt>
                        <dd class="mt-1 font-mono text-sm text-ink">{{ $domain->ip_address ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-ink-muted">Sertifikat SSL</dt>
                        <dd class="mt-1">
                            @if ($domain->ssl_status)
                                <x-badge-status :level="$domain->ssl_status === StatusAktif::Aktif ? 'normal' : 'critical'">SSL {{ $domain->ssl_status->label() }}</x-badge-status>
                            @else
                                <span class="text-sm text-ink">Belum diketahui</span>
                            @endif
                        </dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-medium text-ink-muted">Nameserver</dt>
                        <dd class="mt-1.5">
                            @forelse ($nameservers as $ns)
                                <span class="mr-1.5 mb-1.5 inline-flex rounded-md bg-surface-muted px-2 py-1 font-mono text-xs text-ink">{{ $ns }}</span>
                            @empty
                                <span class="text-sm text-ink">—</span>
                            @endforelse
                        </dd>
                    </div>
                </dl>
            </section>

            <p class="text-xs text-ink-subtle">
                Dicatat {{ $domain->created_at->translatedFormat('d M Y, H:i') }} · Terakhir diperbarui {{ $domain->updated_at->diffForHumans() }}
            </p>
        </div>
    </div>
</x-layouts.app>
