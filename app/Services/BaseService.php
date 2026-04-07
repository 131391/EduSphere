<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

abstract class BaseService
{
    protected string $modelClass;

    /**
     * Get all records
     */
    public function getAll(): Collection
    {
        return $this->modelClass::all();
    }

    /**
     * Get paginated records
     */
    public function paginate(int $perPage = 15)
    {
        return $this->modelClass::paginate($perPage);
    }

    /**
     * Find a record by ID
     */
    public function find(mixed $id): ?Model
    {
        return $this->modelClass::find($id);
    }

    /**
     * Find a record or throw exception
     */
    public function findOrFail(mixed $id): Model
    {
        return $this->modelClass::findOrFail($id);
    }

    /**
     * Create a new record
     */
    public function create(array $data): Model
    {
        return $this->modelClass::create($data);
    }

    /**
     * Update a record
     */
    public function update(mixed $id, array $data): bool
    {
        $model = $this->find($id);
        if (!$model) {
            return false;
        }
        return $model->update($data);
    }

    /**
     * Delete a record
     */
    public function delete(mixed $id): bool
    {
        $model = $this->find($id);
        if (!$model) {
            return false;
        }
        return $model->delete();
    }

    /**
     * Search records
     */
    public function search(string $search = '', string $sortBy = 'created_at', string $direction = 'desc')
    {
        $query = $this->modelClass::query();

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                foreach ($this->getSearchFields() as $field) {
                    $q->orWhere($field, 'like', "%{$search}%");
                }
            });
        }

        return $query->orderBy($sortBy, $direction);
    }

    /**
     * Get fields to search in
     */
    protected function getSearchFields(): array
    {
        return ['name', 'title'];
    }
}
