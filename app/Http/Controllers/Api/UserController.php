<?php

namespace App\Http\Controllers\Api;

use App\Contracts\CreatesNewUser;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateNewUserRequest;
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

    public function store(CreateNewUserRequest $request, CreatesNewUser $creator)
    {
        $this->authorize('create', User::class);

        $user = app(CreatesNewUser::class)->create($request->validated());

        return response()->json(UserResource::create($user), 201);
    }

    public function destroy(Request $request, User $user)
    {
        $this->authorize('delete', $user);

        if ($user->is($request->user())) {
            abort(403, 'You cannot delete yourself.');
        }

        $user->delete();

        return response()->noContent();
    }
}
