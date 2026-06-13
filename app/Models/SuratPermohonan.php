<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SuratPermohonan extends Model
{
    protected $table = 'surat_permohonans';

    protected $fillable = [
        'periode_bantuan_id',
        'paroki_id',
        'stasi_id',
        'document_template_id',
        'generated_by',
        'letter_number',
        'subject',
        'file_path',
        'total_candidates',
        'status',
        'generated_at',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'generated_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    public function calonPenerimas(): BelongsToMany
    {
        return $this->belongsToMany(CalonPenerima::class, 'surat_permohonan_items')->withTimestamps();
    }

    public function periodeBantuan(): BelongsTo
    {
        return $this->belongsTo(PeriodeBantuan::class);
    }

    public function stasi(): BelongsTo
    {
        return $this->belongsTo(Stasi::class);
    }
}
