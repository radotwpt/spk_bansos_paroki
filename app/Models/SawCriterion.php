<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SawCriterion extends Model
{
    use HasFactory;

    protected $table = 'saw_criteria';
    protected $guarded = ['id'];

    public function weights()
    {
        return $this->hasMany(SawWeight::class, 'saw_criterion_id');
    }
}
