<?php

namespace Tests\Feature\Auth;

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
    public function a_user_can_not_authenticate_with_invalid_password()
    {
        $user = User::factory()->create();

        $this->postJson(route('login'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])
            ->assertUnprocessable();

        $this->assertGuest();
    }

    #[Test]
    public function get_the_authenticated_users_data()
    {
        $user = $this->makeUserAndAuthenticateWithToken();

        $response = $this->getJson(route('user'))
            ->assertStatus(200)
            ->assertJsonFragment(['id' => $user->id]);
    }

    #[Test]
    public function an_authenticated_user_can_access_api()
    {
        $user = $this->makeUserAndAuthenticateWithToken();

        $this->getJson(route('ping.auth'))
            ->assertStatus(200)
            ->assertSeeText('pong');

    }
}
