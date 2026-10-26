<?php

namespace Database\Seeders;

use App\Models\Opd;
use Illuminate\Database\Seeder;

class OpdSeeder extends Seeder
{
    /**
     * Data contoh OPD Kota Tomohon (nama kepala dan kontak fiktif).
     */
    public function run(): void
    {
        $daftarOpd = [
            ['Dinas Komunikasi dan Informatika', 'Jl. Raya Tomohon, Kel. Kolongan, Tomohon Tengah', '(0431) 351001', 'Ir. Johny Rumengan'],
            ['Dinas Kesehatan', 'Jl. Sam Ratulangi, Kel. Talete, Tomohon Tengah', '(0431) 351002', 'dr. Maria Wenas'],
            ['Dinas Pendidikan dan Kebudayaan', 'Jl. Pendidikan, Kel. Matani, Tomohon Tengah', '(0431) 351003', 'Drs. Frans Lumi'],
            ['Dinas Kependudukan dan Pencatatan Sipil', 'Jl. Raya Kakaskasen, Tomohon Utara', '(0431) 351004', 'Stevi Pangemanan, S.E.'],
            ['Badan Perencanaan Pembangunan, Penelitian dan Pengembangan Daerah', 'Kompleks Kantor Wali Kota, Tomohon Tengah', '(0431) 351005', 'Dr. Ronald Sumual'],
            ['Badan Pengelolaan Keuangan dan Aset Daerah', 'Kompleks Kantor Wali Kota, Tomohon Tengah', '(0431) 351006', 'Lenny Mamahit, S.E., M.Si.'],
            ['Dinas Pariwisata', 'Jl. Kawasan Wisata Bukit Doa, Tomohon Selatan', '(0431) 351007', 'Hendrik Tumbel, S.Par.'],
            ['Dinas Penanaman Modal dan Pelayanan Terpadu Satu Pintu', 'Jl. Raya Walian, Tomohon Selatan', '(0431) 351008', 'Yance Rondonuwu, S.H.'],
            ['Badan Kepegawaian dan Pengembangan Sumber Daya Manusia', 'Kompleks Kantor Wali Kota, Tomohon Tengah', '(0431) 351009', 'Meiske Kalangi, S.Sos.'],
            ['Sekretariat Daerah', 'Kompleks Kantor Wali Kota, Tomohon Tengah', '(0431) 351010', 'Ir. Albert Warouw, M.T.'],
        ];

        foreach ($daftarOpd as [$nama, $alamat, $kontak, $kepala]) {
            Opd::updateOrCreate(
                ['nama_opd' => $nama],
                ['alamat' => $alamat, 'kontak' => $kontak, 'kepala_opd' => $kepala],
            );
        }
    }
}
