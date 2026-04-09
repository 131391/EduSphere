<?php

namespace App\Livewire\Traits;

trait WithConfirmation
{
    public bool $showConfirmation = false;
    public string $confirmationTitle = '';
    public string $confirmationMessage = '';
    public string $confirmationAction = '';
    public string $confirmButtonText = 'Confirm';
    public string $confirmButtonColor = 'red';
    public ?\Closure $confirmCallback = null;

    public function confirmAction(
        string $title,
        string $message,
        string $action,
        ?callable $callback = null,
        string $buttonText = 'Confirm',
        string $buttonColor = 'red'
    ): void {
        $this->confirmationTitle = $title;
        $this->confirmationMessage = $message;
        $this->confirmationAction = $action;
        $this->confirmCallback = $callback;
        $this->confirmButtonText = $buttonText;
        $this->confirmButtonColor = $buttonColor;
        $this->showConfirmation = true;
    }

    public function cancelConfirmation(): void
    {
        $this->showConfirmation = false;
        $this->confirmCallback = null;
    }

    public function handleConfirmation(): void
    {
        if ($this->confirmCallback && is_callable($this->confirmCallback)) {
            call_user_func($this->confirmCallback);
        } else {
            $this->call($this->confirmationAction);
        }
        $this->cancelConfirmation();
    }

    public function confirmDelete(string $name, ?callable $callback = null): void
    {
        $this->confirmAction(
            'Delete Confirmation',
            "Are you sure you want to delete '{$name}'? This action cannot be undone.",
            'delete',
            $callback,
            'Delete',
            'red'
        );
    }
}
