<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SawWeightItem extends Model
{
    protected $fillable = ['saw_weight_version_id', 'saw_criterion_id', 'weight'];

    protected function casts(): array
    {
        return ['weight' => 'decimal:2'];
    }

    public function criterion(): BelongsTo
    {
        return $this->belongsTo(SawCriterion::class, 'saw_criterion_id');
    }
}
