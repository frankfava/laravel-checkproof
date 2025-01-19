<?php

namespace App\Contracts;

interface CreatesValidator
{
    /**
     * Create a validator
     *
     * @return \Illuminate\Validation\Validator
     */
    public function createValidator(array $data = [], array $customRules = [], array $customMessages = []);
}
