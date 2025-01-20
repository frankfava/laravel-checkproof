<?php

use App\Http\Controllers\Api;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/ping/auth', fn () => 'pong')->name('ping.auth');

Route::get('/user', fn (Request $request) => $request->user())->name('user');

// Manage Users
Route::group(['prefix' => 'users'], function () {
    Route::put('/profile-information/{user}', [Api\UserController::class, 'updateInformation'])->name('users.profile-update');
    Route::put('/password/{user}', [Api\UserController::class, 'updatePassword'])->name('users.password-update');
});
Route::apiResource('users', Api\UserController::class)->names('users')->except(['update']);
