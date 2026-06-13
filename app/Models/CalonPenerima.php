<?php

namespace App\Models;

use App\Traits\OptimizeQueries;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class CalonPenerima extends Model
{
    use SoftDeletes, OptimizeQueries;

    protected $table = 'calon_penerimas';

    protected $fillable = [
        'periode_bantuan_id',
        'paroki_id',
        'stasi_id',
        'lingkungan_id',
        'created_by',
        'submitted_by',
        'validated_by',
        'sent_by',
        'decided_by',
        'registration_number',
        'name',
        'nik',
        'nomor_kk',
        'family_head_name',
        'place_of_birth',
        'date_of_birth',
        'gender',
        'address',
        'phone',
        'occupation',
        'monthly_income',
        'dependents_count',
        'housing_status',
        'housing_status_score',
        'has_disability',
        'disability_score',
        'disability_note',
        'urgency_note',
        'economic_condition_note',
        'status',
        'stasi_validation_note',
        'paroki_decision_note',
        'submitted_at',
        'validated_at',
        'sent_to_paroki_at',
        'ranked_at',
        'decided_at',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'monthly_income' => 'decimal:2',
            'has_disability' => 'boolean',
            'submitted_at' => 'datetime',
            'validated_at' => 'datetime',
            'sent_to_paroki_at' => 'datetime',
            'ranked_at' => 'datetime',
            'decided_at' => 'datetime',
        ];
    }

    /**
     * Get default eager load relations for optimization
     */
    public function getDefaultEagerLoad(): array
    {
        return ['periodeBantuan', 'paroki', 'stasi', 'lingkungan'];
    }

    /**
     * Get columns for list queries
     */
    public function getListSelectColumns(): array
    {
        return [
            'id',
            'periode_bantuan_id',
            'paroki_id',
            'stasi_id',
            'lingkungan_id',
            'registration_number',
            'name',
            'nik',
            'nomor_kk',
            'address',
            'phone',
            'monthly_income',
            'dependents_count',
            'housing_status',
            'has_disability',
            'status',
            'created_at',
            'updated_at',
        ];
    }

    /**
     * Get columns for detail queries
     */
    public function getDetailSelectColumns(): array
    {
        return ['*'];
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return match ($user->role?->name) {
            'ketua_lingkungan_stasi' => $query->where('lingkungan_id', $user->lingkungan_id),
            'stasi' => $query->where('stasi_id', $user->stasi_id),
            'paroki' => $query->where('paroki_id', $user->paroki_id),
            default => $query,
        };
    }

    public function periodeBantuan(): BelongsTo
    {
        return $this->belongsTo(PeriodeBantuan::class);
    }

    public function paroki(): BelongsTo
    {
        return $this->belongsTo(Paroki::class);
    }

    public function stasi(): BelongsTo
    {
        return $this->belongsTo(Stasi::class);
    }

    public function lingkungan(): BelongsTo
    {
        return $this->belongsTo(Lingkungan::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sawResult(): HasOne
    {
        return $this->hasOne(SawResult::class);
    }

    public function penerimaBantuan(): HasOne
    {
        return $this->hasOne(PenerimaBantuan::class);
    }

    public function validasiLogs(): HasMany
    {
        return $this->hasMany(ValidasiLog::class);
    }
}
