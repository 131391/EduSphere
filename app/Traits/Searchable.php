<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * Searchable Trait
 * 
 * Provides search functionality for models
 */
trait Searchable
{
    /**
     * Scope to search across specified fields
     * 
     * @param Builder $query
     * @param string|null $search
     * @param array $fields Fields to search in
     * @return Builder
     */
    public function scopeSearch(Builder $query, ?string $search, array $fields = []): Builder
    {
        if (!$search || empty($fields)) {
            return $query;
        }

        return $query->where(function ($q) use ($search, $fields) {
            foreach ($fields as $field) {
                // Handle nested relationships (e.g., 'user.name')
                if (str_contains($field, '.')) {
                    [$relation, $column] = explode('.', $field, 2);
                    $q->orWhereHas($relation, function ($relationQuery) use ($column, $search) {
                        $relationQuery->where($column, 'like', "%{$search}%");
                    });
                } else {
                    $q->orWhere($field, 'like', "%{$search}%");
                }
            }
        });
    }

    /**
     * Get searchable fields for the model
     * Override this method in your model to define searchable fields
     * 
     * @return array
     */
    public function getSearchableFields(): array
    {
        return $this->searchable ?? [];
    }
}
