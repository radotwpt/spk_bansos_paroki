<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PeriodeBantuan extends Model
{
    use SoftDeletes;

    protected $table = 'periode_bantuans';

    protected $fillable = [
        'paroki_id',
        'code',
        'name',
        'description',
        'aid_type',
        'starts_at',
        'ends_at',
        'quota',
        'ranking_scope_size',
        'default_aid_amount',
        'total_budget',
        'planned_disbursement_date',
        'status',
        'ranking_locked_at',
        'finalized_at',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'ends_at' => 'date',
            'planned_disbursement_date' => 'date',
            'ranking_locked_at' => 'datetime',
            'finalized_at' => 'datetime',
            'default_aid_amount' => 'decimal:2',
            'total_budget' => 'decimal:2',
        ];
    }

    public function paroki(): BelongsTo
    {
        return $this->belongsTo(Paroki::class);
    }

    public function calonPenerimas(): HasMany
    {
        return $this->hasMany(CalonPenerima::class);
    }

    public function sawResults(): HasMany
    {
        return $this->hasMany(SawResult::class);
    }

    public function penerimaBantuans(): HasMany
    {
        return $this->hasMany(PenerimaBantuan::class);
    }
}
