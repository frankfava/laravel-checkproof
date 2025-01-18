<?php

use Illuminate\Support\Facades\Route;

$guard = config('passport.guard', null);

Route::middleware(['web', $guard ? 'auth:'.$guard : 'auth'])->group(function () {

    Route::get('/scopes', [
        'uses' => 'ScopeController@all',
        'as' => 'scopes.index',
    ]);

    Route::get('/personal-access-tokens', [
        'uses' => 'PersonalAccessTokenController@forUser',
        'as' => 'personal.tokens.index',
    ]);

    Route::post('/personal-access-tokens', [
        'uses' => 'PersonalAccessTokenController@store',
        'as' => 'personal.tokens.store',
    ]);

    Route::delete('/personal-access-tokens/{token_id}', [
        'uses' => 'PersonalAccessTokenController@destroy',
        'as' => 'personal.tokens.destroy',
    ]);
});
