<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SawWeightVersion extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'total_weight',
        'effective_from',
        'effective_until',
        'is_active',
        'locked_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'total_weight' => 'decimal:2',
            'effective_from' => 'date',
            'effective_until' => 'date',
            'is_active' => 'boolean',
            'locked_at' => 'datetime',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(SawWeightItem::class);
    }
}
