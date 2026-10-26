<?php

namespace App\Exports;

use App\Models\Domain;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

/**
 * Export laporan domain ke Excel: kop judul, tabel, dan baris total.
 */
class LaporanExport implements FromCollection, ShouldAutoSize, WithCustomStartCell, WithEvents, WithHeadings, WithMapping, WithTitle
{
    /**
     * Baris pertama tabel (heading). Baris 1–4 dipakai untuk kop laporan.
     */
    private const BARIS_HEADING = 6;

    private int $nomor = 0;

    /**
     * @param  array{judul: string, subjudul: string, periode: string|null, domains: Collection<int, Domain>, nama_file: string}  $laporan
     */
    public function __construct(private array $laporan) {}

    public function collection(): BaseCollection
    {
        return $this->laporan['domains']->toBase();
    }

    public function startCell(): string
    {
        return 'A'.self::BARIS_HEADING;
    }

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return ['No', 'Nama Domain', 'OPD', 'Status', 'Tanggal Expired', 'Sisa Waktu', 'Keterangan'];
    }

    /**
     * @param  Domain  $domain
     * @return list<mixed>
     */
    public function map(mixed $domain): array
    {
        return [
            ++$this->nomor,
            $domain->nama_domain,
            $domain->opd->nama_opd,
            $domain->status->label(),
            $domain->tanggal_expired->format('d/m/Y'),
            $domain->label_expired,
            $domain->keterangan ?? '-',
        ];
    }

    /**
     * @return array<class-string, callable>
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $jumlah = $this->laporan['domains']->count();
                $barisTotal = self::BARIS_HEADING + $jumlah + 1;

                $sheet->setCellValue('A1', 'PEMERINTAH KOTA TOMOHON — DINAS KOMUNIKASI DAN INFORMATIKA');
                $sheet->setCellValue('A2', $this->laporan['judul']);
                $sheet->setCellValue('A3', $this->laporan['subjudul'].($this->laporan['periode'] ? ' · Periode expired: '.$this->laporan['periode'] : ''));
                $sheet->setCellValue('A4', 'Tanggal generate: '.now()->translatedFormat('d F Y, H:i').' WITA');

                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(10);
                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A3:A4')->getFont()->setSize(10);

                $heading = 'A'.self::BARIS_HEADING.':G'.self::BARIS_HEADING;
                $sheet->getStyle($heading)->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
                $sheet->getStyle($heading)->getFill()->setFillType('solid')->getStartColor()->setRGB('0369A1');

                $sheet->setCellValue('A'.$barisTotal, 'Total domain');
                $sheet->setCellValue('G'.$barisTotal, $jumlah);
                $sheet->mergeCells('A'.$barisTotal.':F'.$barisTotal);
                $sheet->getStyle('A'.$barisTotal.':G'.$barisTotal)->getFont()->setBold(true);
                $sheet->getStyle('A'.$barisTotal.':G'.$barisTotal)->getFill()->setFillType('solid')->getStartColor()->setRGB('E0F2FE');
            },
        ];
    }

    public function title(): string
    {
        return 'Laporan Domain';
    }
}
