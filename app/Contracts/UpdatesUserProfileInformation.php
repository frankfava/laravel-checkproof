<?php

namespace App\Contracts;

interface UpdatesUserProfileInformation extends CreatesValidator
{
    public function update(\Illuminate\Foundation\Auth\User $user, array $input);
}
