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
        $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
            'role' => ['required', 'string', 'in:masyarakat,pemerintah,admin'],  // validasi role
        ]);

        // cek credentials
        if (!Auth::attempt($request->only('username', 'password'))) {
            return response()->json(['message' => 'Invalid login credentials'], 401);
        }

        // ambil user
        $user = Auth::user();

        $userRole = null;

        if (Masyarakat::where('id', $user->id)->exists()) {
            $userRole = 'masyarakat';
        } elseif (Pemerintah::where('id', $user->id)->exists()) {
            $userRole = 'pemerintah';
        } elseif (Admin::where('id', $user->id)->exists()) {
            $userRole = 'admin';
        }

        // cek role sesuai
        if ($userRole !== $request->role) {
            Auth::logout();
            return response()->json(['message' => 'Unauthorized: Role mismatch'], 403);
        } else{
            // buat session
            $request->session()->regenerate();
        }

        return response()->noContent();
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
