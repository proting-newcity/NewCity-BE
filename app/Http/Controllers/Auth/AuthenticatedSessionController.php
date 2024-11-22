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
    public function store(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
            'role' => ['required', 'string', 'in:masyarakat,pemerintah,admin'],  // Validate the role
        ]);

        // Check the credentials
        if (!Auth::attempt($request->only('username', 'password'))) {
            return response()->json(['message' => 'Invalid login credentials'], 401);
        }

        // Get the authenticated user
        $user = Auth::user();

        // Determine the role of the user by checking associated models
        $userRole = null;

        if (Masyarakat::where('id', $user->id)->exists()) {
            $userRole = 'masyarakat';
        } elseif (Pemerintah::where('id', $user->id)->exists()) {
            $userRole = 'pemerintah';
        } elseif (Admin::where('id', $user->id)->exists()) {
            $userRole = 'admin';
        }

        // Check if the provided role matches the user's actual role
        if ($userRole !== $request->role) {
            return response()->json(['message' => 'Unauthorized: Role mismatch'], 403);
        }

        // Generate the token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Return the response with the role
        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
            'role' => $userRole,  // Add role to the response
            'status' => 'Login successful',
        ]);
    }

    /**
     * Logout and invalidate the token.
     */
    public function destroy(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout successful']);
    }
}
