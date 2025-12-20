<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * Sortable Trait
 * 
 * Provides sorting functionality for models
 */
trait Sortable
{
    /**
     * Scope to sort by column and direction
     * 
     * @param Builder $query
     * @param string|null $column
     * @param string $direction
     * @return Builder
     */
    public function scopeSortBy(Builder $query, ?string $column = null, string $direction = 'asc'): Builder
    {
        $column = $column ?? 'id';
        $direction = strtolower($direction);

        // Validate direction
        if (!in_array($direction, ['asc', 'desc'])) {
            $direction = 'asc';
        }

        // Get sortable columns
        $sortableColumns = $this->getSortableColumns();

        // Validate column
        if (!in_array($column, $sortableColumns)) {
            $column = 'id';
        }

        return $query->orderBy($column, $direction);
    }

    /**
     * Get sortable columns for the model
     * Override this method in your model to define sortable columns
     * 
     * @return array
     */
    public function getSortableColumns(): array
    {
        return $this->sortable ?? ['id', 'created_at', 'updated_at'];
    }

    /**
     * Scope to sort by latest
     */
    public function scopeLatest(Builder $query, string $column = 'created_at'): Builder
    {
        return $query->orderBy($column, 'desc');
    }

    /**
     * Scope to sort by oldest
     */
    public function scopeOldest(Builder $query, string $column = 'created_at'): Builder
    {
        return $query->orderBy($column, 'asc');
    }
}
