<?php

namespace App\Exports;

use App\Models\Opd;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Export tabel pemetaan domain per OPD ke Excel.
 */
class PemetaanExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    private int $nomor = 0;

    /**
     * @param  Collection<int, Opd>  $perOpd  hasil StatistikDomain::perOpd()
     */
    public function __construct(private Collection $perOpd) {}

    public function collection(): BaseCollection
    {
        return $this->perOpd->toBase()->push([
            'total' => true,
            'domains_count' => $this->perOpd->sum('domains_count'),
            'domains_aktif_count' => $this->perOpd->sum('domains_aktif_count'),
            'domains_kritis_count' => $this->perOpd->sum('domains_kritis_count'),
        ]);
    }

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return ['No', 'Nama OPD', 'Kepala OPD', 'Jumlah Domain', 'Aktif', 'Tidak Aktif', 'Kritis (≤ 7 hari / lewat)'];
    }

    /**
     * @return list<mixed>
     */
    public function map(mixed $row): array
    {
        if (is_array($row)) {
            return [
                '',
                'TOTAL',
                '',
                $row['domains_count'],
                $row['domains_aktif_count'],
                $row['domains_count'] - $row['domains_aktif_count'],
                $row['domains_kritis_count'],
            ];
        }

        return [
            ++$this->nomor,
            $row->nama_opd,
            $row->kepala_opd ?? '-',
            $row->domains_count,
            $row->domains_aktif_count,
            $row->domains_count - $row->domains_aktif_count,
            $row->domains_kritis_count,
        ];
    }

    public function styles(Worksheet $sheet): ?array
    {
        $barisTotal = $this->perOpd->count() + 2;

        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '0369A1']],
            ],
            $barisTotal => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return 'Pemetaan Domain OPD';
    }
}
