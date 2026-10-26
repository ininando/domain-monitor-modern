<?php

namespace Database\Factories;

use App\Models\Domain;
use App\Models\Opd;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Domain>
 */
class DomainFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tanggalRegistrasi = fake()->dateTimeBetween('-5 years', '-1 month');

        return [
            'opd_id' => Opd::factory(),
            'nama_domain' => fake()->unique()->domainWord().'.tomohonkota.go.id',
            'tanggal_registrasi' => $tanggalRegistrasi,
            'tanggal_expired' => fake()->dateTimeBetween('+31 days', '+2 years'),
            'status' => 'aktif',
            'registrar' => 'PANDI',
            'nameserver' => "ns1.tomohonkota.go.id\nns2.tomohonkota.go.id",
            'ip_address' => fake()->ipv4(),
            'hosting_provider' => 'Server Diskominfo Tomohon',
            'ssl_status' => 'aktif',
            'keterangan' => null,
        ];
    }

    /**
     * Domain yang akan expired dalam $hari hari (negatif = sudah lewat).
     */
    public function expiredDalam(int $hari): static
    {
        return $this->state(fn (array $attributes) => [
            'tanggal_expired' => today()->addDays($hari),
        ]);
    }

    /**
     * Domain berstatus tidak aktif.
     */
    public function tidakAktif(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'tidak_aktif',
        ]);
    }
}
