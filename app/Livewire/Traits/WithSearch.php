<?php

namespace App\Livewire\Traits;

trait WithSearch
{
    public string $search = '';
    public string $sortBy = 'created_at';
    public string $sortDirection = 'desc';
    public array $searchFields = [];

    public function updatedSearch(): void
    {
        $this->resetPagination();
    }

    public function setSortBy(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function resetSearch(): void
    {
        $this->search = '';
        $this->sortBy = 'created_at';
        $this->sortDirection = 'desc';
        $this->resetPagination();
    }

    public function isSorted(string $field): bool
    {
        return $this->sortBy === $field;
    }

    public function isSortedAsc(string $field): bool
    {
        return $this->sortBy === $field && $this->sortDirection === 'asc';
    }

    public function isSortedDesc(string $field): bool
    {
        return $this->sortBy === $field && $this->sortDirection === 'desc';
    }
}
