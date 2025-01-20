<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EnsureUserIsActiveMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_allows_requests_to_unprotected_routes()
    {
        $this->getJson(route('ping.guest'))->assertOk();
    }

    #[Test]
    public function it_allows_requests_for_active_users_on_protected_routes()
    {
        $this->makeUserAndAuthenticateWithToken(role : 'admin');

        $this->getJson(route('ping.auth'))
            ->assertOk();
    }

    #[Test]
    public function it_denies_requests_for_inactive_users_on_protected_routes()
    {
        $this->makeUserAndAuthenticateWithToken(role : 'admin', active : false);

        $this->getJson(route('ping.auth'))
            ->assertStatus(403)
            ->assertJson([
                'message' => 'Your account is inactive. Please contact support.',
            ]);
    }

    #[Test]
    public function it_allows_requests_for_unprotected_routes_when_user_is_inactive()
    {
        $this->makeUserAndAuthenticateWithToken(role : 'admin', active : false);

        $this->getJson(route('ping.guest'))
            ->assertOk();
    }
}
