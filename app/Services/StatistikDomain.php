<?php

namespace App\Services;

use App\Enums\LevelExpired;
use App\Enums\StatusAktif;
use App\Models\Domain;
use App\Models\Opd;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;

/**
 * Kumpulan query statistik untuk dashboard, pemetaan, dan notifikasi.
 */
class StatistikDomain
{
    /**
     * Jumlah slice donut sebelum sisanya digabung menjadi "Lainnya".
     */
    public const MAKS_SLICE_DONUT = 5;

    /**
     * Ringkasan angka utama dashboard.
     *
     * @return array{total: int, aktif: int, tidak_aktif: int, akan_expired: int, sudah_expired: int}
     */
    public function ringkasan(int $hari = 30): array
    {
        $total = Domain::count();
        $aktif = Domain::where('status', StatusAktif::Aktif)->count();

        return [
            'total' => $total,
            'aktif' => $aktif,
            'tidak_aktif' => $total - $aktif,
            'akan_expired' => Domain::akanExpired($hari)->count(),
            'sudah_expired' => Domain::sudahExpired()->count(),
        ];
    }

    /**
     * Domain yang perlu perhatian (sudah lewat atau expired dalam $hari), paling mendesak dulu.
     *
     * @return Collection<int, Domain>
     */
    public function perluPerhatian(int $hari = 30, ?int $batas = 8): Collection
    {
        return Domain::query()
            ->with('opd:id,nama_opd')
            ->whereDate('tanggal_expired', '<=', today()->addDays($hari))
            ->orderBy('tanggal_expired')
            ->when($batas, fn ($query) => $query->limit($batas))
            ->get();
    }

    /**
     * Jumlah domain kritis (≤ 7 hari atau sudah lewat) untuk lonceng notifikasi.
     */
    public function jumlahKritis(): int
    {
        return Domain::level(LevelExpired::Critical)->count();
    }

    /**
     * Semua OPD beserta jumlah domain total, aktif, dan kritis; terurut jumlah domain terbanyak.
     *
     * @return Collection<int, Opd>
     */
    public function perOpd(): Collection
    {
        return Opd::query()
            ->withCount([
                'domains',
                'domains as domains_aktif_count' => fn ($query) => $query->where('status', StatusAktif::Aktif),
                'domains as domains_kritis_count' => fn ($query) => $query->level(LevelExpired::Critical),
            ])
            ->orderByDesc('domains_count')
            ->orderBy('nama_opd')
            ->get();
    }

    /**
     * Data donut: OPD teratas + gabungan "Lainnya" (maks. 6 slice).
     *
     * @param  Collection<int, Opd>  $perOpd
     * @return BaseCollection<int, array{label: string, nilai: int, warna: string}>
     */
    public function donutOpd(Collection $perOpd): BaseCollection
    {
        $berisi = $perOpd->where('domains_count', '>', 0)->values();

        $slices = $berisi->take(self::MAKS_SLICE_DONUT)->values()->map(fn (Opd $opd, int $i): array => [
            'label' => $opd->nama_opd,
            'nilai' => $opd->domains_count,
            'warna' => '--chart-'.($i + 1),
        ]);

        $sisa = $berisi->slice(self::MAKS_SLICE_DONUT);

        if ($sisa->isNotEmpty()) {
            $slices->push([
                'label' => "Lainnya ({$sisa->count()} OPD)",
                'nilai' => $sisa->sum('domains_count'),
                'warna' => 'other',
            ]);
        }

        return $slices;
    }

    /**
     * Pertumbuhan jumlah domain (kumulatif) per bulan selama $bulan bulan terakhir.
     * Tanggal acuan: tanggal registrasi, atau tanggal dicatat jika registrasi kosong.
     *
     * @return array{labels: list<string>, total: list<int>, baru: list<int>}
     */
    public function pertumbuhanBulanan(int $bulan = 12): array
    {
        $awal = CarbonImmutable::today()->startOfMonth()->subMonths($bulan - 1);

        $tanggal = Domain::query()
            ->get(['tanggal_registrasi', 'created_at'])
            ->map(fn (Domain $domain) => CarbonImmutable::parse($domain->tanggal_registrasi ?? $domain->created_at));

        $kumulatif = $tanggal->filter(fn (CarbonImmutable $t) => $t->lt($awal))->count();
        $perBulan = $tanggal
            ->filter(fn (CarbonImmutable $t) => $t->gte($awal))
            ->countBy(fn (CarbonImmutable $t) => $t->format('Y-m'));

        $hasil = ['labels' => [], 'total' => [], 'baru' => []];

        for ($i = 0; $i < $bulan; $i++) {
            $titik = $awal->addMonths($i);
            $baru = $perBulan->get($titik->format('Y-m'), 0);
            $kumulatif += $baru;

            $hasil['labels'][] = $titik->translatedFormat('M Y');
            $hasil['total'][] = $kumulatif;
            $hasil['baru'][] = $baru;
        }

        return $hasil;
    }
}
