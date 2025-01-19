<?php

namespace Tests\Feature;

use App\Actions\Users\CreateNewUser;
use App\Contracts\CreatesNewUser;
use App\Enums\UserRole;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserCreateTest extends TestCase
{
    const VALID_PASSWORD = 'StrongPass1!';

    #[Test]
    public function test_create_new_user_contract_is_bound_to_concrete_implementation(): void
    {
        $resolved = app(CreatesNewUser::class);

        $this->assertInstanceOf(CreateNewUser::class, $resolved);
    }

    #[Test]
    public function can_create_user_with_action(): void
    {
        /** @var \App\Actions\Users\CreateNewUser Resolve interface to concrete action */
        $creator = app(CreatesNewUser::class);

        $user = $creator->createWithValidation(
            data : [
                'name' => 'John Doe',
                'email' => 'john.doe@example.com',
                'password' => 'password', // Weak password
                'password_confirmation' => 'password', // Weak password
                'role' => 'manager',
            ],
            customRules : [
                'password' => 'required|min:8|confirmed', // Override the strong password requirement
            ]
        );

        $this->assertDatabaseHas((new User)->getTable(), [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
        ]);
        $this->assertEquals($user->role, UserRole::Manager);
    }

    #[Test]
    public function user_role_cannot_create_a_user()
    {
        $this->makeUserAndAuthenticateWithToken(role : 'user');

        $this->getJson(route('users.store'))
            ->assertForbidden();
    }

    #[Test]
    public function admin_can_create_a_new_user_with_role_and_active_status()
    {
        $this->makeUserAndAuthenticateWithToken(role : 'admin');

        $userData = User::factory()->make();

        $this->postJson(route('users.store'), [
            'name' => $userData->name,
            'email' => $userData->email,
            'password' => self::VALID_PASSWORD,
            'password_confirmation' => self::VALID_PASSWORD,
            'role' => UserRole::Manager->value,
            'active' => false,
        ])->assertCreated();

        $this->assertDatabaseHas((new User)->getTable(), [
            'email' => $userData->email,
            'role' => UserRole::Manager->value,
            'active' => false,
        ]);
    }

    #[Test]
    public function managers_can_create_a_user_but_cannot_set_role_or_active_status()
    {
        $this->makeUserAndAuthenticateWithToken(role : 'manager');

        $userData = User::factory()->make();

        $this->postJson(route('users.store'), [
            'name' => $userData->name,
            'email' => $userData->email,
            'password' => self::VALID_PASSWORD,
            'password_confirmation' => self::VALID_PASSWORD,
            'role' => UserRole::Manager->value,
            'active' => false,
        ])->assertCreated();

        $this->assertDatabaseMissing((new User)->getTable(), [
            'email' => $userData->email,
            'role' => UserRole::Manager->value,
            'active' => false,
        ]);

        $this->assertDatabaseHas((new User)->getTable(), [
            'email' => $userData->email,
            'role' => UserRole::User->value,
            'active' => true,
        ]);
    }

    #[Test]
    public function must_use_strong_password_when_creating_user_with_route()
    {
        $this->makeUserAndAuthenticateWithToken(role : 'admin');

        $userData = User::factory()->make();

        $this->postJson(route('users.store'), [
            'name' => $userData->name,
            'email' => $userData->email,
            'password' => 'password', // just 8
            'password_confirmation' => 'password', // just 8
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrorFor('password');
    }

    #[Test]
    public function validate_structure()
    {
        $this->makeUserAndAuthenticateWithToken(role : 'admin');

        $userData = User::factory()->make();

        $this->postJson(route('users.store'), [
            'name' => $userData->name,
            'email' => $userData->email,
            'password' => self::VALID_PASSWORD,
            'password_confirmation' => self::VALID_PASSWORD,
        ])
            ->assertCreated()
            ->assertJsonStructure([
                'id',
                'name',
                'email',
                'role',
                'created_at',
            ]);
    }

    #[Test]
    public function password_must_be_confirmed_when_creating_a_user()
    {
        $this->makeUserAndAuthenticateWithToken(role : 'admin');

        $userData = User::factory()->make();

        $this->postJson(route('users.store'), [
            'name' => $userData->name,
            'email' => $userData->email,
            'password' => 'password',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }
}
