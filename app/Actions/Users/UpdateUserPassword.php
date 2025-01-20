<?php

namespace App\Actions\Users;

use App\Contracts\UpdatesUserPasswords;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * Single Action Class to update a user password
 */
class UpdateUserPassword implements UpdatesUserPasswords
{
    use UserValidationRules;

    /**
     * Validate and update the user's password.
     *
     * @param  mixed  $user
     * @return void
     */
    public function update($user, array $input)
    {
        $user->forceFill([
            'password' => Hash::make($input['password']),
        ])->save();
    }

    /** Create a validator for a user information */
    public function createValidator(array $data = [], array $customRules = [], array $customMessages = []): \Illuminate\Validation\Validator
    {
        // Messages
        $messages = [
            ...$this->validationMessages(),
            ...$customMessages,
        ];

        // Validate
        $validator = Validator::make(
            data : $data,
            rules: [
                'current_password' => ['required', 'string'],
                'password' => $this->passwordRules(['required', 'confirmed']),
                ...$customRules,
            ],
            messages: $messages
        );

        return $validator;
    }
}
