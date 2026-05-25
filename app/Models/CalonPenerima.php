<?php

namespace App\Models;

use App\Scopes\TenantDataScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalonPenerima extends Model
{
    use HasFactory;

    protected $table = 'calon_penerimas';

    protected $guarded = ['id'];

    protected $casts = [
        'pendapatan_keluarga' => 'decimal:2',
        'saw_score' => 'decimal:4',
        'is_penerima_sah' => 'boolean',
        'nominal_bansos_disetujui' => 'decimal:2',
    ];

    public function stasi()
    {
        return $this->belongsTo(Stasi::class, 'stasi_id');
    }

    public function lingkunganStasi()
    {
        return $this->belongsTo(LingkunganStasi::class, 'lingkungan_stasi_id');
    }

    public function period()
    {
        return $this->belongsTo(BansosPeriod::class, 'bansos_period_id');
    }

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new TenantDataScope);
    }
}
