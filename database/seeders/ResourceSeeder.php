<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;

class ResourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // For 2-5 random ACTIVE users, create up to 10 orders
        User::all()
            ->where('role', UserRole::User)
            ->where('active', true)
            ->random(rand(2, 5))
            ->each(function ($user) {
                Order::factory()
                    ->for($user)
                    ->count(rand(1, 10))
                    ->create();
            });
    }
}
