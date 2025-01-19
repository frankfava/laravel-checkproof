<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UsersTest extends TestCase
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
}
