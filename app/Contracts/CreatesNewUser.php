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

    /**
     * Validate and create a new user.
     *
     * @return \Illuminate\Foundation\Auth\User
     */
    public function createWithValidation(array $data, array $customRules = [], array $customMessages = []);
}
