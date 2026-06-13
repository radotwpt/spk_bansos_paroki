<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportExport extends Model
{
    protected $fillable = [
        'periode_bantuan_id',
        'paroki_id',
        'stasi_id',
        'generated_by',
        'type',
        'title',
        'filters',
        'file_path',
        'status',
        'generated_at',
    ];

    protected function casts(): array
    {
        return [
            'filters' => 'array',
            'generated_at' => 'datetime',
        ];
    }
}
