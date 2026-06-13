<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ValidasiLog extends Model
{
    protected $table = 'validasi_logs';

    protected $fillable = ['calon_penerima_id', 'actor_id', 'action', 'from_status', 'to_status', 'notes', 'metadata'];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function calonPenerima()
    {
        return $this->belongsTo(CalonPenerima::class, 'calon_penerima_id');
    }
}

