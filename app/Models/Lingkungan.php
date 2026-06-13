<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lingkungan extends Model
{
    protected $fillable = [
        'stasi_id',
        'code',
        'name',
        'chairperson_name',
        'address',
        'phone',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function stasi(): BelongsTo
    {
        return $this->belongsTo(Stasi::class);
    }

    public function calonPenerimas(): HasMany
    {
        return $this->hasMany(CalonPenerima::class);
    }
}
