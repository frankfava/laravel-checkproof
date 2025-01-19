<?php

namespace App\Contracts;

interface CreatesNewUser
{
    /**
     * Create a new user model with trusted data.
     *
     * @return \Illuminate\Foundation\Auth\User
     */
    public function create(array $input);

    /**
     * Validate and create a new user.
     *
     * @return \Illuminate\Foundation\Auth\User
     */
    public function createWithValidation(array $input, array $customRules = [], array $customMessages = []);
}
