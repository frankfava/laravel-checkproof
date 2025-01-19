<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserDeleteTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function you_need_to_be_authenticated_to_delete_a_user()
    {
        $user = $this->makeUser();

        $this->deleteJson(route('users.destroy', $user))
            ->assertUnauthorized();
    }

    #[Test]
    public function admin_can_delete_other_users()
    {
        $admin = $this->makeUserAndAuthenticateWithToken(role: UserRole::Admin);

        $user = User::factory()->manager()->create();

        $this->deleteJson(route('users.destroy', $user))
            ->assertNoContent();

        $this->assertDatabaseMissing((new User)->getTable(), ['id' => $user->id]);
    }

    #[Test]
    public function admin_cannot_delete_self()
    {
        $admin = $this->makeUserAndAuthenticateWithToken(role: UserRole::Admin);

        $this->deleteJson(route('users.destroy', $admin))
            ->assertForbidden();

        $this->assertDatabaseHas((new User)->getTable(), ['id' => $admin->id]);
    }

    #[Test]
    public function managers_cannot_delete_an_admin()
    {
        $manager = $this->makeUserAndAuthenticateWithToken(role: UserRole::Manager);

        $admin = User::factory()->admin()->create();

        $this->deleteJson(route('users.destroy', $admin))
            ->assertForbidden();

        $this->assertDatabaseHas((new User)->getTable(), ['id' => $admin->id]);
    }

    #[Test]
    public function managers_cannot_delete_another_manager()
    {
        $manager1 = $this->makeUserAndAuthenticateWithToken(role: UserRole::Manager);

        $manager2 = User::factory()->manager()->create();

        $this->deleteJson(route('users.destroy', $manager2))
            ->assertForbidden();

        $this->assertDatabaseHas((new User)->getTable(), ['id' => $manager2->id]);
    }

    #[Test]
    public function managers_can_delete_a_user()
    {
        $manager = $this->makeUserAndAuthenticateWithToken(role: UserRole::Manager);

        $user = User::factory()->user()->create();

        $this->deleteJson(route('users.destroy', $user))
            ->assertNoContent();

        $this->assertDatabaseMissing((new User)->getTable(), ['id' => $user->id]);
    }

    #[Test]
    public function users_cannot_delete_a_user()
    {
        $user1 = $this->makeUserAndAuthenticateWithToken(role: UserRole::User);

        $user2 = User::factory()->user()->create();

        $this->deleteJson(route('users.destroy', $user2))
            ->assertForbidden();

        $this->assertDatabaseHas((new User)->getTable(), ['id' => $user2->id]);
    }
}
