<?php

namespace App\Livewire\Components;

use Livewire\Component;
use Livewire\Attributes\Computed;

class Pagination extends Component
{
    public int $current = 1;
    public int $total = 1;
    public int $perPage = 15;

    #[Computed]
    public function totalPages(): int
    {
        return (int) ceil($this->total / $this->perPage);
    }

    #[Computed]
    public function pages(): array
    {
        $pages = [];
        $total = $this->totalPages();

        if ($total <= 7) {
            $pages = range(1, $total);
        } else {
            $pages[] = 1;
            if ($this->current > 3) {
                $pages[] = '...';
            }

            for ($i = max(2, $this->current - 1); $i <= min($total - 1, $this->current + 1); $i++) {
                $pages[] = $i;
            }

            if ($this->current < $total - 2) {
                $pages[] = '...';
            }
            $pages[] = $total;
        }

        return array_unique($pages);
    }

    public function goTo(int $page): void
    {
        if ($page >= 1 && $page <= $this->totalPages()) {
            $this->dispatch('paginate', page: $page);
        }
    }

    public function render()
    {
        return view('livewire.components.pagination');
    }
}
