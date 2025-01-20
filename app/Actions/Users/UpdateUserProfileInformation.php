<?php

namespace App\Actions\Users;

use App\Contracts\UpdatesUserProfileInformation;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * Single Action Class to update a user password
 */
class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    use UserValidationRules;

    public function __construct(
        private User $user
    ) {}

    /** Updates users info with trusted data */
    public function update($user, array $input)
    {
        $update = [
            'name' => $input['name'] ??= $user->name,
            'email' => $input['email'] ??= $user->email,
            'role' => isset($input['role']) ? $input['role'] : $user->role,
            'active' => isset($input['active']) ? (bool) $input['active'] : $user->active,
        ];

        $user->forceFill($update)->save();

        return $user;
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
                'name' => $this->nameRules(['sometimes', 'required']),
                'email' => $this->emailRules(['sometimes', 'required', Rule::unique('users')]),
                'role' => ['prohibited'],
                ...$customRules,
            ],
            messages: $messages
        );

        return $validator;
    }
}
