<?php

namespace Database\Factories;

use App\Models\BansosPeriod;
use App\Models\CalonPenerima;
use App\Models\LingkunganStasi;
use App\Models\Stasi;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CalonPenerima>
 */
class CalonPenerimaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'bansos_period_id' => BansosPeriod::factory(),
            'stasi_id' => Stasi::factory(),
            'lingkungan_stasi_id' => LingkunganStasi::factory(),
            'nik' => fake()->numerify('################'),
            'nama_lengkap' => fake()->name(),
            'alamat_kristen' => fake()->address(),
            'pendapatan_keluarga' => fake()->numberBetween(100000, 5000000),
            'jumlah_tanggungan' => fake()->numberBetween(0, 5),
            'status_tempat_tinggal' => fake()->randomElement(['milik_sendiri', 'sewa', 'numpang']),
            'status_hubungan' => fake()->randomElement(['lajang', 'menikah', 'cerai']),
            'urgensi_tambahan_tekstual' => fake()->optional()->sentence(),
            'status_alur' => 'draft',
            'saw_score' => null,
            'is_penerima_sah' => false,
        ];
    }
}
