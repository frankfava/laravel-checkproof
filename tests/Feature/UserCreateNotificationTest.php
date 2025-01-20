<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\Notifiables\SystemAdmin;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserCreateNotificationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('mail.default', 'smtp');
        Config::set('mail.mailers.smtp', [
            'transport' => 'smtp',
            'scheme' => 'smtp',
            'host' => '127.0.0.1',
            'port' => '1025',
            'encryption' => null,
            'username' => null,
            'password' => null,
            'timeout' => null,
        ]);
    }

    #[Test]
    public function creating_a_new_user_fires_an_eloquent_event()
    {
        Event::fake();

        User::factory()->create();

        Event::assertDispatched('eloquent.created: '.User::class);
        Event::assertListening('eloquent.created: '.User::class, \App\Listeners\NewUserCreated::class);
    }

    #[Test]
    public function sends_mail_to_user_when_user_is_created()
    {
        Notification::fake();

        User::factory()->create();

        Notification::assertSentTo(User::first(), \App\Notifications\NewUserWelcome::class);
    }

    #[Test]
    public function sends_mail_to_system_admin_when_user_is_created()
    {
        Notification::fake();

        User::factory()->create();

        $systemAdmin = new SystemAdmin(collect(config('notifications.system_admins', []))->first());

        Notification::assertSentTo($systemAdmin, \App\Notifications\NewUserAdminNotify::class);

    }
}
