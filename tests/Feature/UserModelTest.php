<?php

namespace Tests\Feature;

use App\Contracts\CreatesNewUser;
use App\Enums\UserRole;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    #[Test]
    public function user_role_scope(): void
    {
        User::factory()
            ->count(3)
            ->active()
            ->user()
            ->create();

        User::factory()
            ->count(3)
            ->manager()
            ->active()
            ->create();

        $users = User::query()
            ->active()
            ->NotByRoles([UserRole::User])
            ->get();

        $this->assertCount(3, $users);
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
