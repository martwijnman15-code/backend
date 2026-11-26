<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\Customer;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', User::class);

        return UserResource::collection(User::query()->when($request->query('search'), fn ($query, $search) => $query->where('name', 'like', "%{$search}%"))->get());
    }

    public function store(UserRequest $request)
    {
        Gate::authorize('store', User::class);

        return new UserResource(User::create($request->validated()));
    }

    public function show(User $user)
    {
        Gate::authorize('view', $user);

        return new UserResource($user);
    }

    public function update(UserRequest $request, User $user)
    {
        Gate::authorize('update', $user);
        $user->update($request->validated());

        return response()->noContent();
    }

    public function destroy(User $user)
    {
        Gate::authorize('delete', $user);
        if ($user->id === request()->user()->id) {
            return response()->json(['message' => 'You cannot delete your own account.'], 422);
        }
        if (Customer::where('user_id', $user->id)->exists() || Project::where('user_id', $user->id)->exists() || DB::table('customers_before_api_repair')->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'This user is linked to customer or project records.'], 409);
        }

        $user->delete();

        return response()->noContent();
    }
}
