<?php

namespace App\Models;

use Database\Factories\OpdFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Opd extends Model
{
    /** @use HasFactory<OpdFactory> */
    use HasFactory;

    /**
     * Nama tabel (tunggal, sesuai spesifikasi).
     *
     * @var string
     */
    protected $table = 'opd';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nama_opd',
        'alamat',
        'kontak',
        'kepala_opd',
    ];

    /**
     * Domain yang dimiliki OPD ini.
     *
     * @return HasMany<Domain, $this>
     */
    public function domains(): HasMany
    {
        return $this->hasMany(Domain::class);
    }

    /**
     * Cari OPD berdasarkan nama, kepala OPD, atau kontak.
     *
     * @param  Builder<Opd>  $query
     */
    public function scopeCari(Builder $query, ?string $kataKunci): void
    {
        $kataKunci = trim((string) $kataKunci);

        if ($kataKunci === '') {
            return;
        }

        $query->where(function (Builder $query) use ($kataKunci): void {
            $query->where('nama_opd', 'like', "%{$kataKunci}%")
                ->orWhere('kepala_opd', 'like', "%{$kataKunci}%")
                ->orWhere('kontak', 'like', "%{$kataKunci}%");
        });
    }
}
