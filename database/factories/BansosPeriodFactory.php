<?php

namespace Database\Factories;

use App\Models\BansosPeriod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BansosPeriod>
 */
class BansosPeriodFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_periode' => fake()->sentence(2),
            'tahun' => fake()->year(),
            'status_periode' => 'aktif',
        ];
    }
}
