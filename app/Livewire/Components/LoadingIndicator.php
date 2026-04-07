<?php

namespace App\Livewire\Components;

use Livewire\Component;

class LoadingIndicator extends Component
{
    public string $message = 'Loading...';

    public function render()
    {
        return view('livewire.components.loading-indicator');
    }
}
