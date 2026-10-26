<?php

namespace App\Services;

use App\Enums\LevelExpired;
use App\Enums\StatusAktif;
use App\Models\Domain;
use App\Models\Opd;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * Menyusun data laporan domain dari parameter yang sudah divalidasi (LaporanRequest).
 *
 * Periode (dari/sampai) memfilter berdasarkan tanggal expired.
 */
class LaporanDomain
{
    /**
     * @param  array{jenis: string, opd_id?: int|string|null, status?: string|null, dari?: string|null, sampai?: string|null, sertakan_akan_expired?: bool|string|null}  $filter
     * @return array{judul: string, subjudul: string, periode: string|null, domains: Collection<int, Domain>, nama_file: string}
     */
    public function susun(array $filter): array
    {
        $jenis = $filter['jenis'];
        $dari = filled($filter['dari'] ?? null) ? CarbonImmutable::parse($filter['dari']) : null;
        $sampai = filled($filter['sampai'] ?? null) ? CarbonImmutable::parse($filter['sampai']) : null;
        $sertakanAkanExpired = filter_var($filter['sertakan_akan_expired'] ?? false, FILTER_VALIDATE_BOOLEAN);

        $query = Domain::query()
            ->with('opd:id,nama_opd')
            ->when($dari, fn ($query) => $query->whereDate('tanggal_expired', '>=', $dari))
            ->when($sampai, fn ($query) => $query->whereDate('tanggal_expired', '<=', $sampai))
            ->orderBy('tanggal_expired')
            ->orderBy('nama_domain');

        [$judul, $subjudul, $slug] = match ($jenis) {
            'opd' => $this->perOpd($query, (int) $filter['opd_id']),
            'status' => $this->perStatus($query, StatusAktif::from($filter['status'])),
            'expired' => $this->expired($query, $sertakanAkanExpired),
        };

        return [
            'judul' => $judul,
            'subjudul' => $subjudul,
            'periode' => $this->teksPeriode($dari, $sampai),
            'domains' => $query->get(),
            'nama_file' => 'laporan-'.$slug.'-'.now()->format('Ymd-His'),
        ];
    }

    /**
     * @return array{string, string, string}
     */
    private function perOpd(Builder $query, int $opdId): array
    {
        $opd = Opd::findOrFail($opdId);
        $query->where('opd_id', $opd->id);

        return ['Laporan Domain per OPD', $opd->nama_opd, 'opd-'.str($opd->nama_opd)->slug()];
    }

    /**
     * @return array{string, string, string}
     */
    private function perStatus(Builder $query, StatusAktif $status): array
    {
        $query->where('status', $status);

        return ['Laporan Domain per Status', 'Status: '.$status->label(), 'status-'.$status->value];
    }

    /**
     * @return array{string, string, string}
     */
    private function expired(Builder $query, bool $sertakanAkanExpired): array
    {
        if ($sertakanAkanExpired) {
            $query->whereDate('tanggal_expired', '<=', today()->addDays(LevelExpired::BATAS_WARNING));

            return ['Laporan Domain Expired', 'Sudah expired dan akan expired dalam '.LevelExpired::BATAS_WARNING.' hari (per '.today()->translatedFormat('d F Y').')', 'expired'];
        }

        $query->sudahExpired();

        return ['Laporan Domain Expired', 'Sudah melewati tanggal expired (per '.today()->translatedFormat('d F Y').')', 'expired'];
    }

    private function teksPeriode(?CarbonImmutable $dari, ?CarbonImmutable $sampai): ?string
    {
        return match (true) {
            $dari && $sampai => $dari->translatedFormat('d F Y').' s.d. '.$sampai->translatedFormat('d F Y'),
            $dari !== null => 'Sejak '.$dari->translatedFormat('d F Y'),
            $sampai !== null => 'Sampai '.$sampai->translatedFormat('d F Y'),
            default => null,
        };
    }
}
