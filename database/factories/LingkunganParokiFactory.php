<?php

namespace Database\Factories;

use App\Models\LingkunganParoki;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LingkunganParoki>
 */
class LingkunganParokiFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => fake()->sentence(2),
            'deskripsi' => fake()->optional()->sentence(),
        ];
    }
}
