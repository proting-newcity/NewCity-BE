<?php

namespace App\Http\Services;

use Illuminate\Support\Facades\Auth;
use App\Services\RoleService;
use Illuminate\Support\Carbon;

class AuthService
{
    public function login(array $credentials, bool $always): array
    {
        if (
            !Auth::attempt([
                'username' => $credentials['username'],
                'password' => $credentials['password'],
            ])
        ) {
            return [
                'error' => 'Invalid login credentials',
                'status' => 401,
            ];
        }

        $user = Auth::user();
        $roleService = app(RoleService::class);
        $userRole = null;

        foreach (['masyarakat', 'pemerintah', 'admin'] as $role) {
            if ($roleService->hasRole($user->id, $role)) {
                $userRole = $role;
                break;
            }
        }

        $newToken = $user->createToken('auth_token');
        $tokenString = $newToken->plainTextToken;
        $tokenModel = $newToken->accessToken;

        // Set token expiration based on always_signed_in flag
        if (!$always) {
            $tokenModel->expires_at = Carbon::now()->addDay();
        }
        
        $tokenModel->save();

        return [
            'data' => [
                'message' => 'Login success',
                'access_token' => $tokenString,
                'token_type' => 'Bearer',
                'role' => $userRole,
            ],
            'status' => 200,
        ];
    }

    /**
     * Logout the current user.
     */
    public function logout(): void
    {
        Auth::guard('web')->logout();
    }
}
