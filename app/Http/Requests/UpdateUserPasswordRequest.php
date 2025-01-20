<?php

namespace App\Http\Requests;

use App\Actions\Users\UserValidationRules;
use App\Contracts\UpdatesUserPasswords;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;

class UpdateUserPasswordRequest extends FormRequest
{
    use UserValidationRules;

    protected User $userToUpdate;

    protected function prepareForValidation()
    {
        if (! $this->offsetExists('user') || ! $this->user()) {
            $this->failedAuthorization();
        }

        $this->userToUpdate = $this->route('user');
    }

    public function authorize(): bool
    {
        return ($user = $this->user()) ? $user->can('update', $this->userToUpdate) : false;
    }

    public function validator()
    {
        return app(UpdatesUserPasswords::class)
            ->createValidator(
                data : $this->all(),
                customRules: [
                    ...$this->validationRules(),
                    'current_password' => [
                        ($this->user()->role != UserRole::User ? 'sometimes' : 'required'),
                        'string',
                    ],
                    'password' => $this->strongPasswordRules(['required', 'confirmed']),
                ],
                customMessages: $this->messages()
            )->after(function ($validator) {
                if ($this->user()->is($this->userToUpdate) && $this->user()->role != UserRole::Admin) {
                    $input = $validator->getData();
                    if (! isset($input['current_password']) || ! Hash::check($input['current_password'], $this->userToUpdate->password)) {
                        $validator->errors()->add('current_password', __('The provided password does not match your current password.'));
                    }
                }
            });
    }
}
