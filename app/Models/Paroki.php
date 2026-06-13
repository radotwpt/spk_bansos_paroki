<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Paroki extends Model
{
    protected $fillable = [
        'code',
        'name',
        'address',
        'phone',
        'email',
        'leader_name',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function stasis(): HasMany
    {
        return $this->hasMany(Stasi::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function periodeBantuans(): HasMany
    {
        return $this->hasMany(PeriodeBantuan::class);
    }
}
