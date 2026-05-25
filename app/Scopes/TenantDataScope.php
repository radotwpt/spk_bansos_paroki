<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class TenantDataScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $user = Auth::user();

        if (! $user || $user->role === 'super_admin' || app()->runningInConsole()) {
            return;
        }

        match ($user->role) {
            'ketua_lingkungan_stasi' => $this->whereIfPresent($builder, 'lingkungan_stasi_id', $user->lingkungan_stasi_id),
            'stasi' => $this->whereIfPresent($builder, 'stasi_id', $user->stasi_id),
            default => null,
        };
    }

    private function whereIfPresent(Builder $builder, string $column, mixed $value): void
    {
        if ($value !== null) {
            $builder->where($column, $value);
        }
    }
}
