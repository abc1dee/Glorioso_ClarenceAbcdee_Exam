<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'full_name'             => 'required|string|max:255',
            'email'                 => 'required|email|unique:users,email',
            'password'              => 'required|min:8|confirmed',
        ]);

        $user = User::create([
            'full_name' => $request->full_name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'role'      => 'guest',
        ]);

        ActivityLogService::log('register', "User {$user->email} registered.");

        return response()->json(['message' => 'Registration successful.'], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        // Check if locked
        if ($user && $user->locked_until && Carbon::now()->lessThan($user->locked_until)) {
            return response()->json(['message' => 'Account locked. Try again in 5 minutes.'], 423);
        }

        // Reset attempts once the lock period has expired
        if ($user && $user->locked_until && Carbon::now()->greaterThanOrEqualTo($user->locked_until)) {
            $user->update(['login_attempts' => 0, 'locked_until' => null]);
        }

        if (!$user || !Hash::check($request->password, $user->password)) {
            if ($user) {
                $user->increment('login_attempts');
                if ($user->login_attempts >= 5) {
                    $user->update(['locked_until' => Carbon::now()->addMinutes(5)]);
                }
            }
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        if (!$user->is_active) {
            return response()->json(['message' => 'Account is deactivated.'], 403);
        }

        // Reset attempts on success
        $user->update(['login_attempts' => 0, 'locked_until' => null, 'last_activity' => Carbon::now()]);

        $token = $user->createToken('auth_token')->plainTextToken;

        ActivityLogService::log('login', "User {$user->email} logged in.");

        return response()->json([
            'token' => $token,
            'user'  => $user,
        ]);
    }

    public function logout(Request $request)
    {
        ActivityLogService::log('logout', "User logged out.");
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully.']);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}
