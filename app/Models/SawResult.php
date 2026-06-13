<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SawResult extends Model
{
    protected $fillable = [
        'periode_bantuan_id',
        'calon_penerima_id',
        'saw_weight_version_id',
        'calculated_by',
        'monthly_income_value',
        'dependents_count_value',
        'housing_status_score_value',
        'disability_score_value',
        'normalized_income',
        'normalized_dependents',
        'normalized_housing',
        'normalized_disability',
        'final_score',
        'rank',
        'calculation_snapshot',
        'calculated_at',
    ];

    protected function casts(): array
    {
        return [
            'monthly_income_value' => 'decimal:2',
            'normalized_income' => 'decimal:6',
            'normalized_dependents' => 'decimal:6',
            'normalized_housing' => 'decimal:6',
            'normalized_disability' => 'decimal:6',
            'final_score' => 'decimal:6',
            'calculation_snapshot' => 'array',
            'calculated_at' => 'datetime',
        ];
    }

    public function calonPenerima(): BelongsTo
    {
        return $this->belongsTo(CalonPenerima::class);
    }

    public function periodeBantuan(): BelongsTo
    {
        return $this->belongsTo(PeriodeBantuan::class);
    }

    public function sawWeightVersion(): BelongsTo
    {
        return $this->belongsTo(SawWeightVersion::class);
    }
}
