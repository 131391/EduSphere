<?php

namespace App\Notifications;

use App\Models\School;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TenantResetPassword extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $token,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $school = $notifiable->school;
        $productName = config('app.name', 'EduSphere');
        $brandName = $school?->name ?: $productName;
        $resetUrl = $this->resetUrl($notifiable, $school);

        return (new MailMessage)
            ->subject("{$brandName} Password Reset Request")
            ->greeting("Hello {$notifiable->name},")
            ->line($school
                ? "We received a password reset request for your {$school->name} account."
                : "We received a password reset request for your {$productName} account.")
            ->line('Click the button below to choose a new password.')
            ->action('Reset Password', $resetUrl)
            ->line('If you did not request a password reset, you can safely ignore this email.')
            ->line($school
                ? "For security, reset your password from {$school->name}'s official school portal."
                : 'For security, only use official reset links from this platform.')
            ->salutation("Regards,\n{$brandName}");
    }

    protected function resetUrl(object $notifiable, ?School $school): string
    {
        $parameters = [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ];

        $relativePath = route('password.reset', $parameters, false);

        if ($school?->subdomain) {
            $appUrl = config('app.url');
            $scheme = request()?->getScheme()
                ?: parse_url($appUrl ?? '', PHP_URL_SCHEME)
                ?: 'http';
            $baseHost = request()?->getHost()
                ?: parse_url($appUrl ?? '', PHP_URL_HOST);

            if ($baseHost) {
                $parts = explode('.', $baseHost);
                if (count($parts) > 1) {
                    array_shift($parts);
                    $tenantHost = $school->subdomain . '.' . implode('.', $parts);

                    return "{$scheme}://{$tenantHost}{$relativePath}";
                }
            }
        }

        return route('password.reset', $parameters, true);
    }
}
