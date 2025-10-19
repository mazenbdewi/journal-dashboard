<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

class CustomResetPassword extends Notification
{
    use Queueable;

    public $token;

    public $user;

    /**
     * Create a new notification instance.
     */
    public function __construct($token, $user)
    {
        $this->token = $token;
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        $resetUrl = $this->buildResetUrl($notifiable);

        $count = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

        return (new MailMessage)
            ->subject(__('Reset Password - إعادة تعيين كلمة المرور'))
            ->view('emails.reset-password', [
                'user' => $notifiable,
                'resetUrl' => $resetUrl,
                'count' => $count,
            ]);
    }

    /**
     * بناء الرابط الصحيح مع الـ Signature
     */
    protected function buildResetUrl($notifiable)
    {
        // Filament 3 يتطلب توقيعاً (signature) للرابط
        return URL::temporarySignedRoute(
            'filament.adminpanel.auth.password-reset.reset',
            Carbon::now()->addMinutes(config('auth.passwords.'.config('auth.defaults.passwords').'.expire')),
            [
                'token' => $this->token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ]
        );
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            //
        ];
    }
}
