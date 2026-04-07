<?php

namespace App\Livewire\Traits;

use Livewire\Attributes\Computed;

trait WithNotifications
{
    public array $notifications = [];

    public function addNotification(string $message, string $type = 'info', int $duration = 5000): void
    {
        $this->notifications[] = [
            'id' => uniqid(),
            'message' => $message,
            'type' => $type, // info, success, warning, error
            'duration' => $duration,
        ];
    }

    public function removeNotification(string $id): void
    {
        $this->notifications = array_filter(
            $this->notifications,
            fn($n) => $n['id'] !== $id
        );
    }

    public function notify(string $message, string $type = 'info'): void
    {
        $this->addNotification($message, $type);
    }

    public function notifySuccess(string $message): void
    {
        $this->addNotification($message, 'success');
    }

    public function notifyError(string $message): void
    {
        $this->addNotification($message, 'error');
    }

    public function notifyWarning(string $message): void
    {
        $this->addNotification($message, 'warning');
    }

    public function clearNotifications(): void
    {
        $this->notifications = [];
    }
}
