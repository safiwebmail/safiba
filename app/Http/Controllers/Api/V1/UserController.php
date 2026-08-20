<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->user()->isAdmin()) {
            return $this->error('Unauthorized', 403);
        }

        $users = User::where('role', '!=', 'customer')
            ->with('shop')
            ->when($request->query('role'), fn ($q, $r) => $q->where('role', $r))
            ->when($request->query('shop_id'), fn ($q, $s) => $q->where('shop_id', $s))
            ->when($request->query('search'), fn ($q, $s) => $q->where(fn ($q) => $q->where('name', 'ilike', "%{$s}%")->orWhere('email', 'ilike', "%{$s}%")))
            ->orderBy('name')
            ->paginate($request->query('per_page', 15));

        return $this->success($users->through(fn ($u) => [
            'id' => $u->id,
            'name' => $u->name,
            'email' => $u->email,
            'phone' => $u->phone,
            'role' => $u->role,
            'shop_id' => $u->shop_id,
            'shop_name' => $u->shop?->name,
            'is_active' => $u->is_active,
            'created_at' => $u->created_at?->toISOString(),
        ]), 'Success');
    }

    public function store(Request $request)
    {
        if (!$request->user()->isAdmin()) {
            return $this->error('Unauthorized', 403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'role' => ['required', 'in:admin,shop_manager,tailor'],
            'shop_id' => ['required_if:role,shop_manager,tailor', 'exists:shops,id'],
        ]);

        $user = User::create($validated);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'user.create',
            'entity_type' => 'user',
            'entity_id' => $user->id,
            'description' => "User '{$user->name}' created with role {$user->role}",
        ]);

        return $this->success(new UserResource($user), 'User created', 201);
    }

    public function update(Request $request, User $user)
    {
        if (!$request->user()->isAdmin()) {
            return $this->error('Unauthorized', 403);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'role' => ['sometimes', 'in:admin,shop_manager,tailor'],
            'shop_id' => ['sometimes', 'nullable', 'exists:shops,id'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $user->update($validated);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'user.update',
            'entity_type' => 'user',
            'entity_id' => $user->id,
            'description' => "User '{$user->name}' updated",
        ]);

        return $this->success(new UserResource($user), 'User updated');
    }

    public function destroy(Request $request, User $user)
    {
        if (!$request->user()->isAdmin()) {
            return $this->error('Unauthorized', 403);
        }

        $user->update(['is_active' => false]);

        return $this->success(null, 'User deactivated');
    }
}
