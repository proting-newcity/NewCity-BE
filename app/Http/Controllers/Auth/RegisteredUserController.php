<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Masyarakat;
use App\Models\Pemerintah;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {

        try {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'username' => ['required', 'string', 'max:255', 'unique:user'],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
                'role' => ['nullable', 'string', 'in:masyarakat,pemerintah'],
                'institusi_id' => [
                    'nullable',
                    'exists:institusi,id',
                    'required_if:role,pemerintah'
                ], // required kalau role pemerintah
                'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }

        $fotoPath = null;

        if ($request->hasFile('foto') && $request->file('foto')->isValid()) {
            $fotoPath= $this->uploadImage($request->file('foto'), public_path('users'));
        }

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'foto' => $fotoPath
        ]);

        if ($request->role) {
            switch ($request->role) {
                case 'masyarakat':
                    Masyarakat::create([
                        'id' => $user->id,
                        'phone' => $request->username,
                    ]);
                    break;

                case 'pemerintah':
                    Pemerintah::create([
                        'id' => $user->id,
                        'status' => true,
                        'phone' => $request->username,
                        'institusi_id' => $request->institusi_id,
                    ]);
                    break;
                default:
                    break;
            }
        }

        event(new Registered($user));

        Auth::login($user);

        return response()->noContent();
    }
}
