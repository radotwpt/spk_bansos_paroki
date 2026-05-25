<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LingkunganStasi extends Model
{
    use HasFactory;

    protected $table = 'lingkungan_stasis';

    protected $guarded = ['id'];

    public function stasi()
    {
        return $this->belongsTo(Stasi::class, 'stasi_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'lingkungan_stasi_id');
    }

    public function calonPenerimas()
    {
        return $this->hasMany(CalonPenerima::class, 'lingkungan_stasi_id');
    }
}
