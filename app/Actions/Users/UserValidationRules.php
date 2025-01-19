<?php

namespace App\Actions\Users;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Validation\Rules\Password;

trait UserValidationRules
{
    /**  Get the validation rules used to validate passwords.  */
    protected function passwordRules($extra = [])
    {
        return array_merge($extra, [
            'string',
            (new Password(8)),
        ]);
    }

    /**  Get the validation rules used to validate Strong passwords.  */
    protected function strongPasswordRules($extra = [])
    {
        return array_merge($extra, [
            'string',
            (new Password(10))
                ->symbols()
                ->mixedCase()
                ->numbers(),
        ]);
    }

    /** Get the validation rules used to validate names. */
    protected function nameRules($extra = [])
    {
        return array_merge($extra, [
            'string', 'min:3', 'max:50',
        ]);
    }

    /** Get the validation rules used to validate emails. */
    protected function emailRules($extra = [])
    {
        return array_merge($extra, [
            'string', 'email', 'max:255',
        ]);
    }

    /** Get the validation rules used to validate exsiting emails. */
    protected function existingEmailRules($extra = [])
    {
        return array_merge($this->emailRules($extra), [
            'exists:users,email',
        ]);
    }

    /** Get the validation rules used to validate unique emails. */
    protected function uniqueEmailRules($extra = [])
    {
        return array_merge($this->emailRules($extra), [
            'unique:users,email',
        ]);
    }

    /** Get the validation rules used to validate user Role */
    protected function roleRules($extra = [])
    {
        return array_merge($extra, [
            'string',
            'in:'.implode(',', UserRole::values()),
        ]);
    }

    /** Get the validation messages. */
    protected function validationMessages($extra = [])
    {
        return array_merge($extra, [
            'email.exists' => 'The email is already registered',
            'password.min' => 'Password must be at least :min characters in length.',
        ]);
    }
}
