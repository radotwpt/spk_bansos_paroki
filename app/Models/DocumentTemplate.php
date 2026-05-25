<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentTemplate extends Model
{
    use HasFactory;

    protected $table = 'document_templates';

    protected $guarded = ['id'];

    protected $fillable = ['name', 'slug', 'type', 'content'];

    public function generatedLetters()
    {
        return $this->hasMany(GeneratedLetter::class, 'document_template_id');
    }
}
