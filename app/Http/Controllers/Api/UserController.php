<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        // Use the very cool custom eloquent Builder
        $query = User::query()
            ->withCount('orders')
            ->where('active', true)
            ->where('role', UserRole::User->value)
            ->sort('created_at', true)
            ->useRequest($request)
            ->mapItems(fn ($user) => $user->append('can_edit'));

        return UserResource::create($query->results());
    }

    public function show(User $user)
    {
        $this->authorize('view', $user);

        return UserResource::create($user);
    }

}
