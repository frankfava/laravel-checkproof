<?php

namespace App\Http\Requests;

use App\Actions\Users\UserValidationRules;
use App\Contracts\CreatesNewUser;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class CreateNewUserRequest extends FormRequest
{
    use UserValidationRules;

    protected function prepareForValidation()
    {
        if (! $this->user()) {
            $this->failedAuthorization();
        }

        // If Authenticated user is not admin, then force default role and active status
        if ($this->user()->role != UserRole::Admin) {
            if ($this->has('role')) {
                $this->replace($this->except('role'));
            }
            if ($this->has('active')) {
                $this->replace($this->except('active'));
            }
        }
    }

    public function authorize(): bool
    {
        return ($user = $this->user()) ? $user->can('create', User::class) : false;
    }

    public function validator()
    {
        return app(CreatesNewUser::class)
            ->createValidator(
                data : $this->all(),
                customRules: [
                    ...$this->validationRules(),
                    'password' => $this->strongPasswordRules(['required', 'confirmed']),
                ],
                customMessages: $this->messages()
            );
    }
}
