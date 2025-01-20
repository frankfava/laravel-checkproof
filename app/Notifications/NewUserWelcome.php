<?php

namespace App\Notifications;

use App\Mail\WelcomeEmail;
use App\Models\User;
use Illuminate\Bus\Queueable;

class NewUserWelcome extends AbstractNotification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(User $notifiable)
    {
        return (new WelcomeEmail($notifiable))->to($notifiable->email);
    }
}
