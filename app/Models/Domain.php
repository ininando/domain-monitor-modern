<?php

namespace App\Models;

use App\Enums\LevelExpired;
use App\Enums\StatusAktif;
use Database\Factories\DomainFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Domain extends Model
{
    /** @use HasFactory<DomainFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'opd_id',
        'nama_domain',
        'tanggal_registrasi',
        'tanggal_expired',
        'status',
        'registrar',
        'nameserver',
        'ip_address',
        'hosting_provider',
        'ssl_status',
        'keterangan',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_registrasi' => 'date',
            'tanggal_expired' => 'date',
            'status' => StatusAktif::class,
            'ssl_status' => StatusAktif::class,
        ];
    }

    /**
     * OPD pemilik domain.
     *
     * @return BelongsTo<Opd, $this>
     */
    public function opd(): BelongsTo
    {
        return $this->belongsTo(Opd::class);
    }

    /**
     * Selisih hari dari hari ini sampai tanggal expired (negatif jika sudah lewat).
     *
     * @return Attribute<int, never>
     */
    protected function sisaHari(): Attribute
    {
        return Attribute::get(
            fn (): int => (int) today()->diffInDays($this->tanggal_expired->copy()->startOfDay(), false)
        );
    }

    /**
     * Level peringatan: Critical (≤ 7 hari / lewat), Warning (8–30), Normal (> 30).
     *
     * @return Attribute<LevelExpired, never>
     */
    protected function levelExpired(): Attribute
    {
        return Attribute::get(fn (): LevelExpired => LevelExpired::dariSisaHari($this->sisa_hari));
    }

    /**
     * Teks countdown yang ramah dibaca, mis. "12 hari lagi" atau "Expired 3 hari lalu".
     *
     * @return Attribute<string, never>
     */
    protected function labelExpired(): Attribute
    {
        return Attribute::get(function (): string {
            $sisaHari = $this->sisa_hari;

            return match (true) {
                $sisaHari < 0 => 'Expired '.abs($sisaHari).' hari lalu',
                $sisaHari === 0 => 'Expired hari ini',
                default => $sisaHari.' hari lagi',
            };
        });
    }

    /**
     * Persentase masa berlaku yang sudah terpakai (0-100), null jika tanggal registrasi kosong.
     *
     * @return Attribute<int|null, never>
     */
    protected function persenMasaBerlaku(): Attribute
    {
        return Attribute::get(function (): ?int {
            if ($this->tanggal_registrasi === null) {
                return null;
            }

            $total = $this->tanggal_registrasi->diffInDays($this->tanggal_expired);

            if ($total <= 0) {
                return 100;
            }

            $terpakai = $this->tanggal_registrasi->diffInDays(today(), false);

            return (int) max(0, min(100, round($terpakai / $total * 100)));
        });
    }

    /**
     * Cari berdasarkan nama domain, IP, atau nama OPD pemilik.
     *
     * @param  Builder<Domain>  $query
     */
    public function scopeCari(Builder $query, ?string $kataKunci): void
    {
        $kataKunci = trim((string) $kataKunci);

        if ($kataKunci === '') {
            return;
        }

        $query->where(function (Builder $query) use ($kataKunci): void {
            $query->where('nama_domain', 'like', "%{$kataKunci}%")
                ->orWhere('ip_address', 'like', "%{$kataKunci}%")
                ->orWhereHas('opd', fn (Builder $opd) => $opd->where('nama_opd', 'like', "%{$kataKunci}%"));
        });
    }

    /**
     * Filter berdasarkan level peringatan expired (batas sama dengan accessor level_expired).
     *
     * @param  Builder<Domain>  $query
     */
    public function scopeLevel(Builder $query, LevelExpired $level): void
    {
        $batasCritical = today()->addDays(LevelExpired::BATAS_CRITICAL);
        $batasWarning = today()->addDays(LevelExpired::BATAS_WARNING);

        match ($level) {
            LevelExpired::Critical => $query->whereDate('tanggal_expired', '<=', $batasCritical),
            LevelExpired::Warning => $query->whereDate('tanggal_expired', '>', $batasCritical)
                ->whereDate('tanggal_expired', '<=', $batasWarning),
            LevelExpired::Normal => $query->whereDate('tanggal_expired', '>', $batasWarning),
        };
    }

    /**
     * Domain yang akan expired dalam rentang hari ini sampai $days hari ke depan.
     *
     * @param  Builder<Domain>  $query
     */
    public function scopeAkanExpired(Builder $query, int $days = 30): void
    {
        $query->whereDate('tanggal_expired', '>=', today())
            ->whereDate('tanggal_expired', '<=', today()->addDays($days));
    }

    /**
     * Domain yang tanggal expired-nya sudah lewat.
     *
     * @param  Builder<Domain>  $query
     */
    public function scopeSudahExpired(Builder $query): void
    {
        $query->whereDate('tanggal_expired', '<', today());
    }
}
