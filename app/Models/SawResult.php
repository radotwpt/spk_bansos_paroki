<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SawResult extends Model
{
    use HasFactory;

    protected $table = 'saw_results';
    protected $guarded = ['id'];
    protected $casts = [
        'raw_values' => 'array',
        'normalized_values' => 'array',
        'weights_used' => 'array',
        'score' => 'decimal:4',
    ];

    public function calon()
    {
        return $this->belongsTo(CalonPenerima::class, 'calon_penerima_id');
    }

    public function period()
    {
        return $this->belongsTo(BansosPeriod::class, 'bansos_period_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
