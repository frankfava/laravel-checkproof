<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserShowTest extends TestCase
{
    #[Test]
    public function you_need_to_be_authenticated_to_view_a_user()
    {
        $user = $this->makeUser(role : 'user');

        $this->getJson(route('users.show', $user))
            ->assertUnauthorized();
    }

    #[Test]
    public function admin_can_view_a_user()
    {
        $this->makeUserAndAuthenticateWithToken(role : 'admin');
        $user = $this->makeUser(role : 'user');

        $this->getJson(route('users.show', [$user]))
            ->assertStatus(200)
            ->assertJsonFragment(['id' => $user->id]);
    }

    #[Test]
    public function manager_can_view_a_user()
    {
        $this->makeUserAndAuthenticateWithToken(role : 'manager');
        $user = $this->makeUser(role : 'user');

        $this->getJson(route('users.show', [$user]))
            ->assertStatus(200)
            ->assertJsonFragment(['id' => $user->id]);
    }

    #[Test]
    public function user_role_can_view_themself()
    {
        $user = $this->makeUserAndAuthenticateWithToken(role : 'user');

        $this->getJson(route('users.show', $user))
            ->assertOk();
    }

    #[Test]
    public function user_role_cannot_view_a_user_that_is_not_them()
    {
        $this->makeUserAndAuthenticateWithToken(role : 'user');

        $user = $this->makeUser(role : 'user');

        $this->getJson(route('users.show', $user))
            ->assertForbidden();
    }

    #[Test]
    public function check_structure_of_response()
    {
        $this->makeUserAndAuthenticateWithToken(role : 'admin');
        $user = $this->makeUser(role : 'user');

        $this->getJson(route('users.show', $user))
            ->assertOk()
            ->assertJsonStructure([
                'id',
                'name',
                'email',
                'role',
                'created_at',
            ]);
    }
}
