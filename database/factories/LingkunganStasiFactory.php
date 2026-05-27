<?php

namespace Database\Factories;

use App\Models\LingkunganStasi;
use App\Models\Stasi;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LingkunganStasi>
 */
class LingkunganStasiFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'stasi_id' => Stasi::factory(),
            'nama_lingkungan_stasi' => fake()->sentence(2),
            'kode_lingkungan' => fake()->unique()->bothify('LS-????'),
        ];
    }
}
