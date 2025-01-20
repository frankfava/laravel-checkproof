<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\InteractsWithQueue;

abstract class AbstractNotification extends Notification
{
    use InteractsWithQueue, Queueable;
}
