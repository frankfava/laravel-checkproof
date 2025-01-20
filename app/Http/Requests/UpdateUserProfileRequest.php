<?php

namespace App\Http\Requests;

use App\Actions\Users\UserValidationRules;
use App\Contracts\UpdatesUserProfileInformation;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserProfileRequest extends FormRequest
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
        return app(UpdatesUserProfileInformation::class)
            ->createValidator(
                data : $this->all(),
                customRules: [
                    ...$this->validationRules(),
                    'role' => $this->roleRules([($this->user()->role == UserRole::Admin && ! $this->user()->is($this->userToUpdate) ? 'sometimes' : 'prohibited')]),
                    'active' => [
                        'boolean',
                        ($this->user()->role == UserRole::Admin && ! $this->user()->is($this->userToUpdate) ? 'sometimes' : 'prohibited'),
                    ],
                ],
                customMessages: $this->messages()
            );
    }
}
