<?php

namespace Tests\Feature\Auth;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->createPersonalAccessClient();
    }

    #[Test]
    public function can_register_user()
    {
        $this->postJson(route('register'), [
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'password' => 'StrongPass1!',
            'password_confirmation' => 'StrongPass1!',
        ])
            ->assertStatus(201)
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email']]);

        $this->assertDatabaseHas('users', [
            'email' => 'johndoe@example.com',
            'role' => 'user',
        ]);
    }

    #[Test]
    public function cannot_register_user_with_missing_fields()
    {
        $this->postJson(route('register'), [
            'email' => 'johndoe@example.com',
            'password' => 'StrongPass1!',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'password']);
    }
}
