<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneratedLetter extends Model
{
    use HasFactory;

    protected $table = 'generated_letters';

    protected $guarded = ['id'];
    protected $casts = [
        'metadata_json' => 'array',
    ];

    public function template()
    {
        return $this->belongsTo(DocumentTemplate::class, 'document_template_id');
    }

    public function calon()
    {
        return $this->belongsTo(CalonPenerima::class, 'calon_penerima_id');
    }

    public function period()
    {
        return $this->belongsTo(BansosPeriod::class, 'bansos_period_id');
    }

    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
