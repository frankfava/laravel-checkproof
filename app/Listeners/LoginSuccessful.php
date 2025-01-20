<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;

class LoginSuccessful
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(Login $event)
    {
        // Example: Send a notification, log, or trigger other actions
        logger()->info("User {$event->user->name} has just logged in.");
    }
}
