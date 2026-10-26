<?php

namespace App\Enums;

enum LevelExpired: string
{
    case Normal = 'normal';
    case Warning = 'warning';
    case Critical = 'critical';

    /**
     * Batas atas sisa hari (inklusif) untuk level Critical.
     */
    public const BATAS_CRITICAL = 7;

    /**
     * Batas atas sisa hari (inklusif) untuk level Warning.
     */
    public const BATAS_WARNING = 30;

    /**
     * Tentukan level dari jumlah sisa hari (boleh negatif).
     */
    public static function dariSisaHari(int $sisaHari): self
    {
        return match (true) {
            $sisaHari <= self::BATAS_CRITICAL => self::Critical,
            $sisaHari <= self::BATAS_WARNING => self::Warning,
            default => self::Normal,
        };
    }

    /**
     * Nama level untuk ditampilkan di antarmuka.
     */
    public function label(): string
    {
        return match ($this) {
            self::Normal => 'Normal',
            self::Warning => 'Warning',
            self::Critical => 'Critical',
        };
    }
}
