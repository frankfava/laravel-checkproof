<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
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
        // Passport Setup
        Passport::personalAccessTokensExpireIn(now()->addMonths(6));
        Passport::useClientModel(\App\Models\Passport\Client::class);

        // Bind our actions to an implementation
        app()->singleton(\App\Contracts\CreatesNewUser::class, \App\Actions\Users\CreateNewUser::class);
        app()->singleton(\App\Contracts\UpdatesUserPasswords::class, \App\Actions\Users\UpdateUserPassword::class);
        app()->singleton(\App\Contracts\UpdatesUserProfileInformation::class, \App\Actions\Users\UpdateUserProfileInformation::class);

        // Events
        Event::listen(\Illuminate\Auth\Events\Login::class, \App\Listeners\LoginSuccessful::class);
        Event::listen('eloquent.created: '.\App\Models\User::class, \App\Listeners\NewUserCreated::class);
    }
}
