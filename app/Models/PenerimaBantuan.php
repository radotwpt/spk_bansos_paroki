<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenerimaBantuan extends Model
{
    protected $table = 'penerima_bantuans';

    protected $fillable = [
        'periode_bantuan_id',
        'calon_penerima_id',
        'decided_by',
        'final_status',
        'aid_amount',
        'aid_description',
        'disbursement_status',
        'payment_method',
        'bank_name',
        'bank_account_number',
        'bank_account_holder',
        'scheduled_disbursement_at',
        'disbursed_at',
        'decision_note',
        'decided_at',
    ];

    protected function casts(): array
    {
        return [
            'aid_amount' => 'decimal:2',
            'scheduled_disbursement_at' => 'datetime',
            'disbursed_at' => 'datetime',
            'decided_at' => 'datetime',
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

    public function decider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }
}
