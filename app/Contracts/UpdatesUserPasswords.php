<?php

namespace App\Contracts;

interface UpdatesUserPasswords extends CreatesValidator
{
    public function update(\Illuminate\Foundation\Auth\User $user, array $input);
}
