<?php

namespace Database\Factories;

use App\Models\Opd;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Opd>
 */
class OpdFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_opd' => 'Dinas '.fake()->unique()->words(2, true),
            'alamat' => fake()->address(),
            'kontak' => fake()->numerify('(0431) ######'),
            'kepala_opd' => fake()->name(),
        ];
    }
}
