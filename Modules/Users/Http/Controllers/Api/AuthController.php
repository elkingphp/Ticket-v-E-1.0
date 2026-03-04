<?php

namespace Modules\Users\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Modules\Users\Domain\Models\User;
use Modules\Users\Http\Resources\Api\UserResource;
use Illuminate\Validation\ValidationException;
use App\Models\User as UserModel; // Fallback alias

class AuthController extends Controller
{
    /**
     * Login User and create token
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            // Check legacy User model if modular not found (optional migration step)
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        if ($user->status !== 'active') {
            return response()->json(['message' => 'Account is not active'], 403);
        }

        // Clean previous tokens for this device if needed or just add new
        // $user->tokens()->where('name', $request->device_name)->delete();

        $token = $user->createToken($request->device_name)->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'token' => $token,
                'user' => new UserResource($user),
            ]
        ]);
    }

    /**
     * Register User
     *
     * SECURITY: Public registration is DISABLED for this system.
     * User accounts are created by administrators via the admin panel only.
     */
    public function register(Request $request)
    {
        abort(403, 'Public registration is disabled. Please contact your administrator.');
    }


    /**
     * Logout User (Revoke the token)
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Get authenticated user profile
     */
    public function profile(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => new UserResource($request->user())
        ]);
    }
}