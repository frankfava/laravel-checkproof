<?php

namespace App\Listeners;

use App\Models\User;
use App\Notifications\NewUserAdminNotify;
use App\Notifications\NewUserWelcome;
use App\Notifications\Notifiables\SystemAdmin;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class NewUserCreated implements ShouldHandleEventsAfterCommit, ShouldQueue
{
    use Queueable;

    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(User $user)
    {
        // Get System admins
        $systemAdmins = collect(config('notifications.system_admins', []))
            ->filter(fn ($d) => filter_var($d['email'], FILTER_VALIDATE_EMAIL))
            ->map(fn ($d) => new SystemAdmin($d))
            ->toArray();

        // Send notification to User
        $user->notify(new NewUserWelcome);

        // Send notification to System Admins
        Notification::send($systemAdmins, new NewUserAdminNotify(createdUser: $user));
    }
}
