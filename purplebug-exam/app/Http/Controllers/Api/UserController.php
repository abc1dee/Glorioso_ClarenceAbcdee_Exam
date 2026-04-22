<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        return response()->json(User::all());
    }

    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|min:8',
            'role'      => 'required|in:admin,guest',
        ]);

        $user = User::create([
            'full_name' => $request->full_name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'role'      => $request->role,
            'is_active' => $request->has('is_active') ? $request->is_active : true,
        ]);

        ActivityLogService::log('create_user', "Created user {$user->email}");
        return response()->json($user, 201);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'full_name' => 'sometimes|string',
            'email'     => "sometimes|email|unique:users,email,{$user->id}",
            'role'      => 'sometimes|in:admin,guest',
            'is_active' => 'sometimes|boolean',
        ]);

        $user->update($request->only(['full_name', 'email', 'role', 'is_active']));
        ActivityLogService::log('update_user', "Updated user {$user->email}");
        return response()->json($user);
    }

    public function destroy(User $user)
    {
        ActivityLogService::log('delete_user', "Deleted user {$user->email}");
        $user->delete();
        return response()->json(['message' => 'User deleted.']);
    }

    public function deactivate(User $user)
    {
        $user->update(['is_active' => false]);
        ActivityLogService::log('deactivate_user', "Deactivated user {$user->email}");
        return response()->json(['message' => 'User deactivated.']);
    }
}
