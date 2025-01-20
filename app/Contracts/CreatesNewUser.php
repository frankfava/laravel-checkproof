<?php

namespace App\Contracts;

interface CreatesNewUser extends CreatesValidator
{
    /**
     * Create a new user model with trusted data.
     *
     * @return \Illuminate\Foundation\Auth\User
     */
    public function create(array $data);
}
