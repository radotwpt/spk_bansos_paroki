<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SawWeight extends Model
{
    use HasFactory;

    protected $table = 'saw_weights';
    protected $guarded = ['id'];
    protected $casts = [
        'weight' => 'float',
    ];

    public function criterion()
    {
        return $this->belongsTo(SawCriterion::class, 'saw_criterion_id');
    }

    public function period()
    {
        return $this->belongsTo(BansosPeriod::class, 'bansos_period_id');
    }
}
