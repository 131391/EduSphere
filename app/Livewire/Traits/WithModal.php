<?php

namespace App\Livewire\Traits;

use Livewire\Attributes\Computed;

trait WithModal
{
    public bool $isOpen = false;
    public string $modalId = '';
    public ?array $modalData = null;

    public function openModal(string $id, ?array $data = null): void
    {
        $this->modalId = $id;
        $this->modalData = $data;
        $this->isOpen = true;
    }

    public function closeModal(): void
    {
        $this->isOpen = false;
        $this->modalId = '';
        $this->modalData = null;
    }

    public function toggleModal(string $id): void
    {
        if ($this->modalId === $id && $this->isOpen) {
            $this->closeModal();
        } else {
            $this->openModal($id);
        }
    }
}
