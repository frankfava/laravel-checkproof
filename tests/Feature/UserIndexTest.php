<?php

namespace Tests\Feature;

use App\Builders\EloquentBuilder;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserIndexTest extends TestCase
{
    #[Test]
    public function user_model_uses_custom_eloquent_builder()
    {
        $query = User::query();

        $this->assertInstanceOf(EloquentBuilder::class, $query);
    }

    #[Test]
    public function you_need_to_be_authenticated_to_list_users()
    {
        $this->getJson(route('users.index'))
            ->assertUnauthorized();
    }

    #[Test]
    public function user_role_cannot_list_users()
    {
        $this->makeUserAndAuthenticateWithToken(role : 'user');

        $this->getJson(route('users.index'))
            ->assertForbidden();
    }

    #[Test]
    public function admin_can_list_users()
    {
        $this->makeUserAndAuthenticateWithToken(role : 'admin');

        User::factory(4)->user()->create();

        $this->getJson(route('users.index', [
            'per_page' => 2,
            'page' => 1,
        ]))
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    #[Test]
    public function manager_can_list_users()
    {
        $this->makeUserAndAuthenticateWithToken(role : 'manager');

        User::factory(4)->user()->create();

        $this->getJson(route('users.index', [
            'per_page' => 2,
            'page' => 1,
        ]))
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    #[Test]
    public function check_structure_of_response()
    {
        $this->makeUserAndAuthenticateWithToken(role : 'admin');

        User::factory()->user()->active()->create();

        $res = $this->getJson(route('users.index', [
            'per_page' => 2,
            'page' => 1,
        ]))
            ->assertOk();

        $res->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                    'can_edit',
                    'orders_count',
                ],
            ],
            'currentPage',
            'from',
            'lastPage',
            'perPage',
            'to',
            'total',
        ])
            ->assertJsonMissingPath('data.0.password');
    }

    #[Test]
    public function can_list_users_by_searching_name()
    {
        $this->makeUserAndAuthenticateWithToken(role : 'admin');

        User::factory()->user()->active()->count(4)->create();
        User::factory()->user()->active()->create(['name' => 'Aab Smith']);

        $this->getJson(route('users.index', [
            'search' => 'Aab',
        ]))
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    #[Test]
    public function can_list_users_by_searching_email()
    {
        $this->makeUserAndAuthenticateWithToken(role : 'admin');

        User::factory()->user()->active()->count(4)->create();
        User::factory()->user()->active()->create(['email' => 'aab@example.com']);

        $this->getJson(route('users.index', [
            'q' => 'aab@example.com',
        ]))
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    #[Test]
    public function can_list_users_and_sort_by_name()
    {
        $this->makeUserAndAuthenticateWithToken(role : 'admin');

        User::factory()->user()->active()->create(['name' => 'carl']);
        User::factory()->user()->active()->create(['name' => 'aab']);

        $this->getJson(route('users.index', [
            'sortBy' => 'name',
            'sortDesc' => false,
        ]))
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.name', 'aab');
    }

    #[Test]
    public function can_list_users_and_sort_by_email()
    {
        $this->makeUserAndAuthenticateWithToken(role : 'admin');

        User::factory()->user()->active()->create(['email' => 'carl@example.com']);
        User::factory()->user()->active()->create(['email' => 'aab@example.com']);

        $this->getJson(route('users.index', [
            'sortBy' => 'email',
            'sortDesc' => false,
        ]))
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.email', 'aab@example.com');

    }

    #[Test]
    public function can_list_users_and_sort_by_orders_count()
    {
        $this->makeUserAndAuthenticateWithToken(role : 'admin');

        User::factory()->user()->count(3)->active()->create();

        User::factory()->user()->active()
            ->has(Order::factory(2))
            ->create(['email' => 'aab@example.com']);

        $this->getJson(route('users.index', [
            'sortBy' => 'orders_count',
            'sortDesc' => true,
        ]))
            ->assertOk()
            ->assertJsonCount(4, 'data')
            ->assertJsonPath('data.0.email', 'aab@example.com');

    }

    #[Test]
    public function can_list_users_and_show_the_last_created_first_by_default()
    {
        $this->makeUserAndAuthenticateWithToken(role : 'admin');

        // first Created
        $user1 = User::factory()->user()->active()->create();

        // Go Forward a day
        Carbon::setTestNow(now()->addDay());

        // Last created
        $user2 = User::factory()->user()->active()->create();

        $this->getJson(route('users.index'))
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $user2->id);
    }

    #[Test]
    public function can_list_users_and_only_return_active_users()
    {
        $this->makeUserAndAuthenticateWithToken(role : 'admin');

        User::factory()->user()->active()->active()->create();
        User::factory()->user()->active()->inactive()->create();

        $res = $this->getJson(route('users.index'))
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }
}
