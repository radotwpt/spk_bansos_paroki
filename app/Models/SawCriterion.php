<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SawCriterion extends Model
{
    protected $table = 'saw_criteria';

    protected $fillable = ['code', 'name', 'type', 'attribute_key', 'description', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function options(): HasMany
    {
        return $this->hasMany(SawCriterionOption::class);
    }
}
