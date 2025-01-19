<?php

namespace App\Actions\Users;

use App\Contracts\CreatesNewUser;
use App\Contracts\CreatesValidator;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Single Action Class to create a new user
 * Allow programattic creation of new user
 *
 * Use for both admin management and registration
 */
class CreateNewUser implements CreatesNewUser, CreatesValidator
{
    use UserValidationRules;

    /** Create a new user model with trusted data */
    public function create(array $data)
    {
        // User
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => isset($data['role']) ? $data['role'] : UserRole::default(),
            'active' => isset($data['active']) ? (bool) $data['active'] : true,
        ]);

        // @todo: Fire Custom Event if verification is no used

        return $user;
    }

    /** Create a new user model with validated data */
    public function createWithValidation(array $data, array $customRules = [], array $customMessages = [])
    {
        $validator = $this->createValidator($data, $customRules, $customMessages);

        // Validation Error
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $this->create($validator->validated());
    }

    /**
     * Create a validator for a New user
     * Also used in \App\Http\Requests\UserRequest
     */
    public function createValidator(array $data = [], array $customRules = [], array $customMessages = []): \Illuminate\Validation\Validator
    {
        $rules = [
            'name' => $this->nameRules(['required']),
            'email' => $this->uniqueEmailRules(['required']),
            'password' => $this->strongPasswordRules(['required', 'confirmed']),
            'role' => $this->roleRules(['sometimes']),
            ...$customRules,
        ];

        // Messages
        $messages = [
            ...$this->validationMessages(),
            ...$customMessages,
        ];

        // Validate
        $validator = Validator::make($data, $rules, $messages);

        return $validator;
    }
}
