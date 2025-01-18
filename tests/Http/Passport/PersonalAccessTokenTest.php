<?php

namespace Tests\Http\Passport;

use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PersonalAccessTokenTest extends TestCase
{
    protected ClientRepository $clients;

    protected function setUp(): void
    {
        parent::setUp();

        Passport::$scopes = [
            'sample-scope' => 'Place orders',
        ];

        $this->clients = app(ClientRepository::class);

        $this->createPersonalAccessClient();
    }

    #[Test]
    public function get_scopes(): void
    {
        $this->makeUserAndAuthenticate(guard : 'web');

        $response = $this->getJson(route('passport.scopes.index'))
            ->assertJsonCount(1);
    }

    #[Test]
    public function get_all_personal_tokens_user_has_created(): void
    {
        $user = $this->makeUserAndAuthenticate(guard : 'web');

        /** @var \Laravel\Passport\PersonalAccessTokenResult $token */
        $token = $user->createToken($this->personalAccessClient->name, ['sample-scope']);

        $response = $this->getJson(route('passport.personal.tokens.index'))
            ->assertJsonCount(1)
            ->assertJson([
                [
                    'name' => $this->personalAccessClient->name,
                    'scopes' => [
                        'sample-scope',
                    ],
                ],
            ]);
    }

    #[Test]
    public function store_new_personal_tokens_on_global_client(): void
    {
        $this->makeUserAndAuthenticate(guard : 'web');

        $data = [
            'name' => 'Token Name',
            'scopes' => [],
        ];

        // Uses Most recently created personal access client

        $response = $this->postJson(route('passport.personal.tokens.store'), $data)
            ->assertOk()
            ->assertJsonStructure([
                'accessToken',
                'token',
            ]);
    }

    #[Test]
    public function delete_personal_tokens_user_has_created(): void
    {
        $user = $this->makeUserAndAuthenticate(guard : 'web');

        /** @var \Laravel\Passport\PersonalAccessTokenResult $token */
        $token = $user->createToken($this->personalAccessClient->name, ['sample-scope']);

        $response = $this->deleteJson(route('passport.personal.tokens.destroy', [$token->token->id]))
            ->assertNoContent();
    }

    #[Test]
    public function can_ping_api_with_personal_access_token(): void
    {
        $this->makeUserAndAuthenticateWithToken();

        $response = $this
            ->getJson('/api/ping')
            ->assertSeeText('pong');
    }

    #[Test]
    public function cannot_ping_api_without_personal_access_token(): void
    {
        $response = $this
            ->getJson('/api/ping')
            ->assertStatus(401)
            ->assertDontSeeText('pong');
    }
}
