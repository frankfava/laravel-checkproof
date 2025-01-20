<?php

namespace App\Notifications;

use App\Mail\AdminNotified;
use App\Models\User;
use Illuminate\Bus\Queueable;

class NewUserAdminNotify extends AbstractNotification
{
    use Queueable;

    public User $createdUser;

    public function __construct(User $createdUser)
    {
        $this->createdUser = $createdUser;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable)
    {
        return (new AdminNotified($this->createdUser))->to($notifiable->email);
    }
}
