<?php

namespace Database\Factories;

use App\Models\Stasi;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Stasi>
 */
class StasiFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_stasi' => fake()->sentence(2),
            'kode_stasi' => fake()->unique()->bothify('ST-????'),
            'alamat' => fake()->optional()->address(),
        ];
    }
}
