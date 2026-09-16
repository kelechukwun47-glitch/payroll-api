<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;


class AuthController extends Controller
{
    /**
     * Handle authentication and token generation.
     */
    public function login(LoginRequest $request): array
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials do not match our records.'],
            ]);
        }

        // Generate Sanctum Bearer Token
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user->load('employee')),
        ];
    }

    /**
     * Get details of the authenticated user.
     */
    public function me(Request $request): UserResource
    {
        return new UserResource($request->user()->load('employee'));
    }

    /**
     * Revoke access token.
     */
    public function logout(Request $request): array
    {
        $request->user()->currentAccessToken()->delete();

        return [
            'message' => 'Successfully logged out.',
        ];
    }
}