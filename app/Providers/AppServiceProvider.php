<?php

namespace App\Providers;

use App\Actions\Users\CreateNewUser;
use App\Contracts\CreatesNewUser;
use App\Models\Passport\Client;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Passport::ignoreRoutes();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Passport::personalAccessTokensExpireIn(now()->addMonths(6));
        Passport::useClientModel(Client::class);

        // Bind our action to an implementation
        app()->singleton(CreatesNewUser::class, CreateNewUser::class);
    }
}
