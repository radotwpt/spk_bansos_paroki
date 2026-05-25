<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stasi extends Model
{
    use HasFactory;

    protected $table = 'stasis';

    protected $guarded = ['id'];

    public function lingkunganStasis()
    {
        return $this->hasMany(LingkunganStasi::class, 'stasi_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'stasi_id');
    }

    public function calonPenerimas()
    {
        return $this->hasMany(CalonPenerima::class, 'stasi_id');
    }
}
