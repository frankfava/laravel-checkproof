<?php

namespace App\Http\Controllers\Auth;

use App\Contracts\CreatesNewUser;
use App\Http\Responses\AuthResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class RegistrationController extends Controller
{
    /**
     * Attempt to authenticate a new session.
     *
     * @return mixed
     */
    public function store(Request $request, CreatesNewUser $creator)
    {

        $validator = $creator->createValidator(
            data : [
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password,
                'password_confirmation' => $request->password_confirmation,
            ],
            customRules : [
                'role' => 'prohibited',
                'active' => 'prohibited',
            ]
        );

        $user = $creator->create($validator->validated());

        $token = $user->createToken('auth_token');

        return new AuthResponse($user, $token, 'User registered successfully', 201);
    }
}
