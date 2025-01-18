<?php

use App\Http\Controllers\Auth;
use Illuminate\Support\Facades\Route;

Route::post('/login', [Auth\AuthenticatedSessionController::class, 'store'])->name('login');
Route::post('/register', [Auth\RegistrationController::class, 'store'])->name('register');
