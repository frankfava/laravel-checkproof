<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Client;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase, WithFaker;

    protected bool $usingLiveDb = false;

    protected ?Client $personalAccessClient = null;

    /** Decode Response */
    protected function content($response, $asArray = true)
    {
        return json_decode($response->content(), (bool) $asArray) ?? $response->content();
    }

    /** Get Original Response  */
    protected function original(\Illuminate\Testing\TestResponse $response)
    {
        $original = $response->original;

        return ($original instanceof JsonResource) ? $original->resource : $original;
    }

    protected function useLiveDB()
    {
        DB::purge(config('database.default'));

        config(['database.connections.sqlite.database' => database_path('database.sqlite')]);

        DB::setDefaultConnection(config('database.default'));

        $this->usingLiveDb = true;
    }

    /**
     * Make a User
     *
     * @return \App\Models\User
     */
    protected function makeUser(...$args)
    {
        $user = User::factory()->create($args);

        return $user;
    }

    /**
     * Make and authenticate User
     *
     * @return \App\Models\User
     */
    protected function makeUserAndAuthenticate(...$args)
    {
        $guard = null;
        if (isset($args['guard'])) {
            $guard = $args['guard'];
            unset($args['guard']);
        }
        $user = $this->makeUser(...$args);
        $this->actingAs($user, $guard ?? 'api');

        return $user;
    }

    /**
     * Authenticate User with Pasport Token
     *
     * @return \App\Models\User
     */
    protected function authenticateUserWithToken(User $user, $scopes = [])
    {
        if (! $this->personalAccessClient) {
            $this->createPersonalAccessClient();
        }

        Passport::actingAs($user, $scopes);

        return $user;
    }

    /**
     * Make and authenticate User
     *
     * @return \App\Models\User
     */
    protected function makeUserAndAuthenticateWithToken(...$args)
    {
        $scopes = [];
        if (isset($args['scopes'])) {
            $scopes = $args['scopes'];
            unset($args['scopes']);
        }
        $user = $this->makeUser(...$args);
        $this->authenticateUserWithToken($user, $scopes);

        return $user;
    }

    /**
     * Specifiy Currently Authenticate Client
     */
    protected function createPersonalAccessClient(...$args): Client
    {
        $client = app(ClientRepository::class)->createPersonalAccessClient(
            ...$args,
            userId: null,
            name: 'Test PAC',
            redirect: url('/'),
        );
        Config::set('passport.personal_access_client.id', $client->id);
        Config::set('passport.personal_access_client.secret', $client->plainSecret);

        $this->personalAccessClient = $client;

        return $client;
    }
}
