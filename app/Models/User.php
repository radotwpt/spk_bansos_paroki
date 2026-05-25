<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $guarded = ['id'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function stasi()
    {
        return $this->belongsTo(Stasi::class, 'stasi_id');
    }

    public function lingkunganParoki()
    {
        return $this->belongsTo(LingkunganParoki::class, 'lingkungan_paroki_id');
    }

    public function lingkunganStasi()
    {
        return $this->belongsTo(LingkunganStasi::class, 'lingkungan_stasi_id');
    }

    public function generatedLetters()
    {
        return $this->hasMany(GeneratedLetter::class, 'created_by');
    }
}
