<?php

namespace App\Livewire\Components;

use Livewire\Component;
use Livewire\Attributes\Computed;

class SearchFilter extends Component
{
    public string $search = '';
    public string $sortBy = 'name';
    public string $sortDirection = 'asc';

    #[Computed]
    public function results()
    {
        return collect([
            // Your search logic here
        ]);
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

    public function render()
    {
        return view('livewire.components.search-filter');
    }
}
