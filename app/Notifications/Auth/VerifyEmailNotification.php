<?php

namespace App\Notifications\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class VerifyEmailNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Verify Your JLPT Master Email')
            ->greeting('Hi ' . $notifiable->full_name . '!')
            ->line('Welcome to JLPT Master. Please verify your email address.')
            ->action('Verify Email', $verificationUrl)
            ->line('This link expires in 60 minutes.')
            ->line('If you did not create an account, ignore this email.');
    }

    protected function verificationUrl(object $notifiable): string
    {
        // $frontendUrl = config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:3000'));

        $apiUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            [
                'id'   => $notifiable->getKey(),
                'hash' => sha1($notifiable->email),
            ]
        );
        return $apiUrl;
        // Point to frontend which will call the API
        // return $frontendUrl . '/verify-email?link=' . urlencode($apiUrl);
    }
}