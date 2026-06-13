<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SawCriterionOption extends Model
{
    protected $table = 'saw_criterion_options';

    protected $fillable = ['saw_criterion_id', 'value', 'label', 'score', 'sort_order'];

    protected function casts(): array
    {
        return ['score' => 'decimal:2'];
    }

    public function criterion(): BelongsTo
    {
        return $this->belongsTo(SawCriterion::class, 'saw_criterion_id');
    }
}
