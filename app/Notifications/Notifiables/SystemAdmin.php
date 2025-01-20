<?php

namespace App\Notifications\Notifiables;

use Illuminate\Auth\GenericUser;
use Illuminate\Notifications\Notifiable;

class SystemAdmin extends GenericUser
{
    use Notifiable;

    public function routeNotificationForMail()
    {
        return $this->name ? [$this->email => $this->name] : $this->email;
    }

    // Notifiable MUST HAVE getKey method
    public function getKey()
    {
        return $this->email;
    }
}
