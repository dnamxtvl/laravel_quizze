<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerifyEmailRegister extends Notification
{
    public function __construct(
        private readonly string $verifyCode
    ) {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Mã OTP xác thực email')
            ->line('Đây là mã OTP của bạn:')
            ->line($this->verifyCode)
            ->line('Mã này sẽ hết hạn sau một thời gian.');
    }
}
