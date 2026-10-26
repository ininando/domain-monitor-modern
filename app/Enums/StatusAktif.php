<?php

namespace App\Enums;

enum StatusAktif: string
{
    case Aktif = 'aktif';
    case TidakAktif = 'tidak_aktif';

    /**
     * Teks untuk ditampilkan di antarmuka.
     */
    public function label(): string
    {
        return match ($this) {
            self::Aktif => 'Aktif',
            self::TidakAktif => 'Tidak Aktif',
        };
    }

    /**
     * Opsi untuk elemen <select>: [nilai => label].
     *
     * @return array<string, string>
     */
    public static function opsi(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $status): array => [$status->value => $status->label()])
            ->all();
    }
}
