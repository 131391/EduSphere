<?php

namespace App\Livewire\Components;

use Livewire\Component;
use Livewire\Attributes\Computed;

class Modal extends Component
{
    public bool $isOpen = false;
    public string $title = 'Modal';
    public string $size = 'md'; // sm, md, lg, xl, 2xl
    public bool $showCloseButton = true;
    public bool $closeOnBackdropClick = true;
    public array $slots = [];

    public function close(): void
    {
        $this->isOpen = false;
    }

    public function open(): void
    {
        $this->isOpen = true;
    }

    #[Computed]
    public function sizeClass(): string
    {
        return match ($this->size) {
            'sm' => 'max-w-sm',
            'md' => 'max-w-md',
            'lg' => 'max-w-lg',
            'xl' => 'max-w-xl',
            '2xl' => 'max-w-2xl',
            default => 'max-w-md',
        };
    }

    public function render()
    {
        return view('livewire.components.modal');
    }
}
