<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LingkunganParoki extends Model
{
    use HasFactory;

    protected $table = 'lingkungan_parokis';

    protected $guarded = ['id'];

    public function users()
    {
        return $this->hasMany(User::class, 'lingkungan_paroki_id');
    }
}
