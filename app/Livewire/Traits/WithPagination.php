<?php

namespace App\Livewire\Traits;

use Livewire\Attributes\Computed;

trait WithPagination
{
    public int $perPage = 15;
    public int $currentPage = 1;
    public string $paginationPath = '';

    public function setPerPage(int $perPage): void
    {
        $this->perPage = max(5, min(100, $perPage)); // Between 5-100
        $this->currentPage = 1;
    }

    public function goToPage(int $page): void
    {
        $this->currentPage = max(1, $page);
    }

    public function nextPage(): void
    {
        $this->currentPage++;
    }

    public function previousPage(): void
    {
        $this->currentPage = max(1, $this->currentPage - 1);
    }

    public function resetPagination(): void
    {
        $this->currentPage = 1;
    }
}
