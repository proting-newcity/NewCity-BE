<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Masyarakat;
use App\Models\Pemerintah;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Log;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        Log::info('Reached the store method');
        
        // Validate the incoming request
        try {
            $validatedData = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'username' => ['required', 'string', 'max:255', 'unique:user'],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
                'role' => ['nullable', 'string', 'in:masyarakat,pemerintah'],
                'phone' => ['nullable', 'string', 'max:255'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }

        // Create the User record
        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'password' => Hash::make($request->password),
        ]);

        // Check if the user has a role and create a role-specific model entry
        if ($request->role) {
            switch ($request->role) {
                case 'masyarakat':
                    // Create a Masyarakat record
                    Masyarakat::create([
                        'id' => $user->id,
                        'phone' => $request->phone ?? null,  // Assuming phone is provided in request
                    ]);
                    break;

                case 'pemerintah':
                    // Create a Pemerintah record
                    Pemerintah::create([
                        'id' => $user->id,
                        'status' => $request->status ?? true,  // Assuming status is provided, default to true
                        'phone' => $request->phone ?? null,    // Assuming phone is provided in request
                        'institusi_id' => $request->institusi_id,  // Assuming institusi_id is provided
                    ]);
                    break;
            }
        }

        // Fire the Registered event
        event(new Registered($user));

        // Create a token for the user
        $token = $user->createToken('auth_token')->plainTextToken;

        // Return response with the token and user info
        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
            'role' => $request->role,  // Return the role in the response
        ]);
    }
}
