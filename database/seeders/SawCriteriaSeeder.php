<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SawCriterion;
use App\Models\SawWeight;

class SawCriteriaSeeder extends Seeder
{
    public function run(): void
    {
        $criteria = [
            ['key' => 'c1_pendapatan', 'label' => 'Pendapatan Keluarga', 'type' => 'cost', 'weight' => 0.40],
            ['key' => 'c2_tanggungan', 'label' => 'Jumlah Tanggungan', 'type' => 'benefit', 'weight' => 0.30],
            ['key' => 'c3_tempat_tinggal', 'label' => 'Status Tempat Tinggal', 'type' => 'benefit', 'weight' => 0.15],
            ['key' => 'c4_status_hubungan', 'label' => 'Status Hubungan', 'type' => 'benefit', 'weight' => 0.15],
        ];

        foreach ($criteria as $c) {
            $criterion = SawCriterion::firstOrCreate(
                ['key' => $c['key']],
                ['label' => $c['label'], 'type' => $c['type']]
            );

            // create global weight (null period)
            SawWeight::updateOrCreate(
                ['saw_criterion_id' => $criterion->id, 'bansos_period_id' => null],
                ['weight' => $c['weight']]
            );
        }
    }
}
