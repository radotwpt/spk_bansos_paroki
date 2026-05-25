<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BansosPeriod extends Model
{
    use HasFactory;

    protected $table = 'bansos_periods';

    protected $guarded = ['id'];

    public function calonPenerimas()
    {
        return $this->hasMany(CalonPenerima::class, 'bansos_period_id');
    }
}
