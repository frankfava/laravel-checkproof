<?php

namespace Tests\Http\Auth;

use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->createPersonalAccessClient();
    }

    #[Test]
    public function a_user_can_authenticate_using_their_email_and_password()
    {
        $user = User::factory()->create();

        $this->postJson(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ])
            ->assertOk()
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email']]);
    }

    #[Test]
    public function login_with_invalid_credentials()
    {
        $this->postJson(route('login'), [
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpassword',
        ])->assertUnprocessable();
    }
}
