<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\Masyarakat;
use App\Models\Pemerintah;
use App\Models\Admin;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request)
{
    try {
        $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
            'always_signed_in' => ['required', 'boolean'],
        ]);

        if (!Auth::attempt($request->only('username', 'password'))) {
            return response()->json(['message' => 'Invalid login credentials'], 401);
        }

        $user = Auth::user();

        if (Masyarakat::where('id', $user->id)->exists()) {
            $userRole = 'masyarakat';
        } elseif (Pemerintah::where('id', $user->id)->exists()) {
            $userRole = 'pemerintah';
        } elseif (Admin::where('id', $user->id)->exists()) {
            $userRole = 'admin';
        }

        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json([
            'message' => 'Login success',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'role' => $userRole,
        ]);
        
        // return response()->noContent();
    } catch (\Exception $e) {
        \Log::error('Error during login', ['exception' => $e]);
        return response()->json([
            'message' => 'An error occurred while processing your request.',
            'error' => $e->getMessage()
        ], 500);
    }
}



    /**
     * Logout and invalidate the token.
     */
    public function destroy(Request $request): Response
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->noContent();
    }
}
