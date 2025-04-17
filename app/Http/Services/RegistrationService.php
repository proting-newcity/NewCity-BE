<?php

namespace App\Http\Services;

use App\Models\User;
use App\Models\Masyarakat;
use App\Models\Pemerintah;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\UploadedFile;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Str;

class RegistrationService
{
    /**
     * Handle user registration.
     *
     * @param array $data
     * @param UploadedFile|null $photo
     * @return User
     */
    public function register(array $data, ?UploadedFile $photo = null): User
    {
        // Handle photo upload
        $photoPath = null;
        if ($photo && $photo->isValid()) {
            $folder = 'users';
            $filename = Str::random(20) . '.' . $photo->extension();
            $photo->storeAs($folder, $filename, 'public');
            $photoPath = "storage/{$folder}/{$filename}";
        }

        // Create base user
        $user = User::create([
            'name'     => $data['name'],
            'username' => $data['username'],
            'password' => Hash::make($data['password']),
            'foto'     => $photoPath,
        ]);

        // Assign role model
        if (!empty($data['role'])) {
            if ($data['role'] === 'masyarakat') {
                Masyarakat::create([
                    'id'    => $user->id,
                    'phone' => $user->username,
                ]);
            } elseif ($data['role'] === 'pemerintah') {
                Pemerintah::create([
                    'id'           => $user->id,
                    'status'       => true,
                    'phone'        => $user->username,
                    'institusi_id' => $data['institusi_id'],
                ]);
            }
        }

        // Fire registered event and login
        event(new Registered($user));
        auth()->login($user);

        return $user;
    }
}
