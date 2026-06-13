<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\Paginator;

/**
 * Trait for optimizing database queries
 * Implements eager loading, selective columns, and pagination strategies
 */
trait OptimizeQueries
{
    /**
     * Define default eager load relations
     *
     * @return array<string>
     */
    public function getDefaultEagerLoad(): array
    {
        return [];
    }

    /**
     * Define selectable columns for list queries
     *
     * @return array<string>
     */
    public function getListSelectColumns(): array
    {
        return ['*'];
    }

    /**
     * Define selectable columns for detail queries
     *
     * @return array<string>
     */
    public function getDetailSelectColumns(): array
    {
        return ['*'];
    }

    /**
     * Apply optimization to query builder
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $relations Eager load relations
     * @param array $columns Specific columns to select
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOptimized(Builder $query, array $relations = [], array $columns = []): Builder
    {
        // Use default eager load if not specified
        if (empty($relations)) {
            $relations = $this->getDefaultEagerLoad();
        }

        // Use default columns if not specified
        if (empty($columns)) {
            $columns = $this->getListSelectColumns();
        }

        return $query
            ->select($columns)
            ->with($relations);
    }

    /**
     * Apply optimization for detail view
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $relations
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOptimizedDetail(Builder $query, array $relations = []): Builder
    {
        if (empty($relations)) {
            $relations = $this->getDefaultEagerLoad();
        }

        return $query
            ->select($this->getDetailSelectColumns())
            ->with($relations);
    }

    /**
     * Apply advanced search with full-text search
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $search Search term
     * @param array $searchFields Fields to search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearchable(Builder $query, ?string $search = null, array $searchFields = []): Builder
    {
        if (!$search) {
            return $query;
        }

        if (empty($searchFields)) {
            $searchFields = ['name', 'email'];
        }

        // Try full-text search first if available
        if (method_exists($this, 'getTable')) {
            try {
                return $query->whereFullText($searchFields, $search, ['mode' => 'boolean']);
            } catch (\Exception $e) {
                // Fall back to LIKE search if full-text not available
            }
        }

        // Fall back to LIKE search
        return $query->where(function ($q) use ($search, $searchFields) {
            foreach ($searchFields as $field) {
                $q->orWhere($field, 'like', "%{$search}%");
            }
        });
    }

    /**
     * Apply advanced filtering
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filters Associative array of filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFiltered(Builder $query, array $filters = []): Builder
    {
        foreach ($filters as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            // Handle date range filters
            if (str_contains($key, 'from_')) {
                $field = str_replace('from_', '', $key);
                $query->where($field, '>=', $value);
            } elseif (str_contains($key, 'to_')) {
                $field = str_replace('to_', '', $key);
                $query->where($field, '<=', $value);
            }
            // Handle array filters (IN clause)
            elseif (is_array($value)) {
                $query->whereIn($key, $value);
            }
            // Handle range filters (e.g., ['min' => 1000, 'max' => 5000])
            elseif (is_array($value) && isset($value['min'], $value['max'])) {
                $query->whereBetween($key, [$value['min'], $value['max']]);
            }
            // Default equality filter
            else {
                $query->where($key, $value);
            }
        }

        return $query;
    }

    /**
     * Apply efficient pagination
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int|null $perPage Items per page
     * @param string $pageName Pagination parameter name
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function scopePaginateEfficient(Builder $query, ?int $perPage = null, string $pageName = 'page')
    {
        $perPage = $perPage ?? 15;

        // Limit max per_page to prevent resource exhaustion
        $perPage = min($perPage, 100);

        return $query->paginate($perPage, ['*'], $pageName);
    }
}
