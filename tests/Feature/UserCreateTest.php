<?php

namespace Tests\Feature;

use App\Actions\Users\CreateNewUser;
use App\Contracts\CreatesNewUser;
use App\Enums\UserRole;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserCreateTest extends TestCase
{
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
                'password' => 'password',
                'password_confirmation' => 'password',
                'role' => 'manager',
            ],
            customRules : [
                'password' => 'required|min:8|confirmed', // Override the strong password requirement
            ]
        );

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
        ]);
        $this->assertEquals($user->role, UserRole::Manager);
    }
}
