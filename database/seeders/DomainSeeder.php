<?php

namespace Database\Seeders;

use App\Models\Domain;
use App\Models\Opd;
use Illuminate\Database\Seeder;

class DomainSeeder extends Seeder
{
    /**
     * 30 domain contoh dengan variasi tanggal expired relatif terhadap hari ini:
     * sudah lewat, <= 7 hari, 8-30 hari, dan > 30 hari.
     */
    public function run(): void
    {
        $opd = Opd::orderBy('id')->pluck('id')->values();

        /**
         * [subdomain, indeks OPD, sisa hari, umur registrasi (bulan), status, ssl]
         *
         * @var list<array{0: string, 1: int, 2: int, 3: int, 4: string, 5: string|null}>
         */
        $daftarDomain = [
            ['diskominfo', 0, 210, 23, 'aktif', 'aktif'],
            ['ppid', 0, 25, 20, 'aktif', 'aktif'],
            ['satudata', 0, 5, 14, 'aktif', 'aktif'],
            ['mail', 0, 340, 22, 'aktif', 'aktif'],
            ['jdih', 0, -12, 18, 'aktif', 'tidak_aktif'],
            ['dinkes', 1, 14, 21, 'aktif', 'aktif'],
            ['sikda', 1, 2, 16, 'aktif', 'aktif'],
            ['puskesmas', 1, 95, 9, 'aktif', 'aktif'],
            ['disdikbud', 2, 60, 19, 'aktif', 'aktif'],
            ['ppdb', 2, -3, 12, 'aktif', 'aktif'],
            ['perpustakaan', 2, 150, 6, 'tidak_aktif', null],
            ['disdukcapil', 3, 0, 17, 'aktif', 'aktif'],
            ['antrian-capil', 3, 9, 8, 'aktif', 'aktif'],
            ['bappelitbangda', 4, 120, 15, 'aktif', 'aktif'],
            ['sipd', 4, 22, 11, 'aktif', 'aktif'],
            ['e-musrenbang', 4, -45, 24, 'tidak_aktif', 'tidak_aktif'],
            ['bpkad', 5, 7, 13, 'aktif', 'aktif'],
            ['simda', 5, 280, 10, 'aktif', 'aktif'],
            ['aset', 5, 30, 7, 'aktif', 'tidak_aktif'],
            ['pariwisata', 6, 18, 20, 'aktif', 'aktif'],
            ['tif', 6, 75, 5, 'aktif', 'aktif'],
            ['festivalbunga', 6, -1, 4, 'aktif', 'aktif'],
            ['dpmptsp', 7, 45, 14, 'aktif', 'aktif'],
            ['oss', 7, 27, 3, 'aktif', 'aktif'],
            ['bkpsdm', 8, 190, 12, 'aktif', 'aktif'],
            ['simpeg', 8, 400, 2, 'aktif', 'aktif'],
            ['setda', 9, 365, 21, 'aktif', 'aktif'],
            ['lpse', 9, 88, 6, 'aktif', 'aktif'],
            ['sirup', 9, 52, 1, 'tidak_aktif', null],
            ['portal', 9, 500, 0, 'aktif', 'aktif'],
        ];

        foreach ($daftarDomain as [$sub, $indeksOpd, $sisaHari, $umurBulan, $status, $ssl]) {
            Domain::updateOrCreate(
                ['nama_domain' => "{$sub}.tomohonkota.go.id"],
                [
                    'opd_id' => $opd[$indeksOpd],
                    'tanggal_registrasi' => today()->subMonthsNoOverflow($umurBulan)->startOfMonth(),
                    'tanggal_expired' => today()->addDays($sisaHari),
                    'status' => $status,
                    'registrar' => 'PANDI',
                    'nameserver' => "ns1.tomohonkota.go.id\nns2.tomohonkota.go.id",
                    'ip_address' => '103.31.'.(10 + $indeksOpd).'.'.(20 + strlen($sub)),
                    'hosting_provider' => $indeksOpd % 3 === 0 ? 'Server Diskominfo Tomohon' : 'Cloud Kominfo (PDN)',
                    'ssl_status' => $ssl,
                    'keterangan' => $status === 'tidak_aktif' ? 'Layanan dinonaktifkan sementara.' : null,
                ],
            );
        }
    }
}
