<?php

namespace App\Livewire\Components;

use Livewire\Component;

class Alert extends Component
{
    public string $type = 'info'; // info, success, warning, error
    public string $message = '';
    public bool $dismissible = true;

    public function dismiss(): void
    {
        $this->dispatch('close-alert');
    }

    public function render()
    {
        return view('livewire.components.alert');
    }
}
