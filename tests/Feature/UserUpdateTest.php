<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserUpdateTest extends TestCase
{
    use RefreshDatabase;

    const VALID_PASSWORD = 'StrongPass1!';

    // ======= User Info

    #[Test]
    public function profile_information_can_be_updated_by_self()
    {
        $user = $this->makeUserAndAuthenticateWithToken(role: UserRole::User, name : 'Original');

        $this->putJson(route('users.profile-update', $user), [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ])->assertOk();

        $this->assertEquals('Test User', $user->fresh()->name);
        $this->assertEquals('test@example.com', $user->fresh()->email);
    }

    #[Test]
    public function a_user_can_only_update_to_a_unique_email()
    {
        $user = $this->makeUserAndAuthenticateWithToken(role: UserRole::User);

        $anotherUser = User::factory()->admin()->create();

        $this->putJson(route('users.profile-update', $user), [
            'email' => $anotherUser->email,
        ])->assertUnprocessable();

        $this->assertNotEquals($user->fresh()->email, $anotherUser->email);
    }

    #[Test]
    public function users_cannot_update_another_user()
    {
        $user = $this->makeUserAndAuthenticateWithToken(role: UserRole::User);

        $anotherUser = User::factory()->user()->create();

        $this->putJson(route('users.profile-update', ['user' => $anotherUser->id]), [
            'name' => 'Test User',
        ])->assertForbidden();

        $this->assertNotEquals('Test User', $anotherUser->fresh()->name);
    }

    #[Test]
    public function admin_can_update_a_users_information()
    {
        $this->makeUserAndAuthenticateWithToken(role: UserRole::Admin);

        $user = User::factory()->user()->active()->create();

        $this->putJson(route('users.profile-update', $user), [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ])->assertOk();

        $user->refresh();

        $this->assertEquals('Test User', $user->name);
        $this->assertEquals('test@example.com', $user->email);
    }

    #[Test]
    public function admin_can_update_a_managers_information()
    {
        $this->makeUserAndAuthenticateWithToken(role: UserRole::Admin);

        $user = User::factory()->manager()->active()->create();

        $this->putJson(route('users.profile-update', $user), [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ])->assertOk();

        $user->refresh();

        $this->assertEquals('Test User', $user->name);
        $this->assertEquals('test@example.com', $user->email);
    }

    #[Test]
    public function admin_can_update_another_admins_information()
    {
        $this->makeUserAndAuthenticateWithToken(role: UserRole::Admin);

        $user = User::factory()->admin()->active()->create();

        $this->putJson(route('users.profile-update', $user), [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ])->assertOk();

        $user->refresh();

        $this->assertEquals('Test User', $user->name);
        $this->assertEquals('test@example.com', $user->email);
    }

    #[Test]
    public function admin_can_update_a_users_role()
    {
        $this->makeUserAndAuthenticateWithToken(role: UserRole::Admin);

        $user = User::factory()->user()->active()->create();

        $this->putJson(route('users.profile-update', $user), [
            'role' => UserRole::Admin->value,
        ])->assertOk();

        $user->refresh();

        $this->assertEquals(UserRole::Admin->value, $user->role->value);
    }

    #[Test]
    public function admin_cannot_update_own_role()
    {
        $admin = $this->makeUserAndAuthenticateWithToken(role: UserRole::Admin);

        $this->putJson(route('users.profile-update', $admin), [
            'role' => UserRole::User->value,
        ])->assertUnprocessable();

        $admin->refresh();

        $this->assertEquals(UserRole::Admin->value, $admin->role->value);
    }

    #[Test]
    public function managers_can_update_a_users_information()
    {
        $this->makeUserAndAuthenticateWithToken(role: UserRole::Manager);

        $user = User::factory()->user()->active()->create();

        $this->putJson(route('users.profile-update', $user), [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ])->assertOk();

        $user->refresh();

        $this->assertEquals('Test User', $user->name);
        $this->assertEquals('test@example.com', $user->email);
    }

    #[Test]
    public function managers_cannot_update_another_managers_information()
    {
        $this->makeUserAndAuthenticateWithToken(role: UserRole::Manager);

        $user = User::factory()->manager()->active()->create();

        $this->putJson(route('users.profile-update', $user), [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ])->assertForbidden();
    }

    #[Test]
    public function managers_cannot_update_an_admin_user_information()
    {
        $this->makeUserAndAuthenticateWithToken(role: UserRole::Manager);

        $user = User::factory()->admin()->active()->create();

        $this->putJson(route('users.profile-update', $user), [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ])->assertForbidden();
    }

    #[Test]
    public function managers_cannot_update_a_users_role()
    {
        $user = $this->makeUserAndAuthenticateWithToken(role: UserRole::Manager);

        $this->putJson(route('users.profile-update', $user), [
            'role' => UserRole::User->value,
        ])->assertUnprocessable();

        $user->refresh();

        $this->assertEquals(UserRole::Manager->value, $user->role->value);

    }

    // ======= Passwords

    #[Test]
    public function admin_can_update_any_users_password_without_current()
    {
        $this->makeUserAndAuthenticateWithToken(role: UserRole::Admin);

        $user = User::factory()->manager()->create();

        $this->putJson(route('users.password-update', $user), [
            'password' => self::VALID_PASSWORD,
            'password_confirmation' => self::VALID_PASSWORD,
        ])->assertOk();

        $this->assertTrue(Hash::check(self::VALID_PASSWORD, $user->fresh()->password));
    }

    #[Test]
    public function admin_can_update_their_own_password_without_current()
    {
        $admin = $this->makeUserAndAuthenticateWithToken(role: UserRole::Admin);

        $this->putJson(route('users.password-update', $admin), [
            'password' => self::VALID_PASSWORD,
            'password_confirmation' => self::VALID_PASSWORD,
        ])->assertOk();

        $this->assertTrue(Hash::check(self::VALID_PASSWORD, $admin->fresh()->password));
    }

    #[Test]
    public function manager_cannot_update_their_own_password_without_current()
    {
        $user = $this->makeUserAndAuthenticateWithToken(role: UserRole::Manager);

        $this->putJson(route('users.password-update', $user), [
            'password' => self::VALID_PASSWORD,
            'password_confirmation' => self::VALID_PASSWORD,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrorFor('current_password');
    }

    #[Test]
    public function manager_can_update_a_users_password_without_current()
    {
        $this->makeUserAndAuthenticateWithToken(role: UserRole::Manager);

        $user = User::factory()->user()->create();

        $this->putJson(route('users.password-update', $user), [
            'password' => self::VALID_PASSWORD,
            'password_confirmation' => self::VALID_PASSWORD,
        ])->assertOk();

        $this->assertTrue(Hash::check(self::VALID_PASSWORD, $user->fresh()->password));
    }

    #[Test]
    public function password_must_be_confirmed_when_updating_users_password()
    {
        $admin = $this->makeUserAndAuthenticateWithToken(role: UserRole::Admin);

        $user = User::factory()->create();

        $this->putJson(route('users.password-update', $user), [
            'password' => self::VALID_PASSWORD,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrorFor('password');
    }

    #[Test]
    public function user_can_update_their_own_password()
    {
        $user = $this->makeUserAndAuthenticateWithToken(role: UserRole::User);

        $this->putJson(route('users.password-update', $user), [
            'current_password' => User::factory()::$defaultPassword,
            'password' => self::VALID_PASSWORD,
            'password_confirmation' => self::VALID_PASSWORD,
        ])->assertOk();
    }

    #[Test]
    public function current_password_must_be_correct()
    {
        $user = $this->makeUserAndAuthenticateWithToken(role: UserRole::User);

        $this->putJson(route('users.password-update', $user), [
            'current_password' => 'wrong-password',
            'password' => self::VALID_PASSWORD,
            'password_confirmation' => self::VALID_PASSWORD,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrorFor('current_password');

        $this->assertTrue(Hash::check(User::factory()::$defaultPassword, $user->fresh()->password));
    }

    #[Test]
    public function new_passwords_must_match()
    {
        $user = $this->makeUserAndAuthenticateWithToken(role: UserRole::User);

        $response = $this->putJson(route('users.password-update', $user), [
            'current_password' => User::factory()::$defaultPassword,
            'password' => self::VALID_PASSWORD,
            'password_confirmation' => 'wrong-'.self::VALID_PASSWORD,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrorFor('password');

        $this->assertTrue(Hash::check(User::factory()::$defaultPassword, $user->fresh()->password));
    }

    #[Test]
    public function passwords_must_be_strong()
    {
        $user = $this->makeUserAndAuthenticateWithToken(role: UserRole::User);

        $this->putJson(route('users.password-update', $user), [
            'current_password' => User::factory()::$defaultPassword,
            'password' => 'new-password', // Valid but weak
            'password_confirmation' => 'new-password',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrorFor('password');

        $this->assertTrue(Hash::check(User::factory()::$defaultPassword, $user->fresh()->password));
    }
}
