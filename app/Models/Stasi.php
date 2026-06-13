<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stasi extends Model
{
    protected $fillable = [
        'paroki_id',
        'code',
        'name',
        'address',
        'phone',
        'leader_name',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function paroki(): BelongsTo
    {
        return $this->belongsTo(Paroki::class);
    }

    public function lingkungans(): HasMany
    {
        return $this->hasMany(Lingkungan::class);
    }

    public function calonPenerimas(): HasMany
    {
        return $this->hasMany(CalonPenerima::class);
    }
}
