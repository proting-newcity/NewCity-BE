<?php

namespace App\Http\Services;

use App\Models\Pemerintah;
use App\Models\Masyarakat;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Traits\ImageUploadTrait;

class AdminService
{
    use ImageUploadTrait;

    /**
     * Create a new Pemerintah account.
     */
    public function storePemerintah(array $data, $foto = null)
    {
        $fotoPath = $foto ? $this->uploadImage($foto, 'users') : null;

        $user = User::create([
            'name'     => $data['name'],
            'username' => $data['username'],
            'password' => Hash::make($data['password']),
            'foto'     => $fotoPath,
        ]);

        Pemerintah::create([
            'id'           => $user->id,
            'status'       => $data['status'],
            'phone'        => $data['phone'],
            'institusi_id' => $data['institusi_id'] ?? null,
        ]);

        event(new \Illuminate\Auth\Events\Registered($user));
    }

    /**
     * Update an existing Pemerintah account.
     */
    public function updatePemerintah($id, array $data, $foto = null)
    {
        $user = User::find($id);
        $pemerintah = Pemerintah::find($id);

        if (!$user || !$pemerintah) {
            return ['error' => 'User or Pemerintah not found', 'error_code' => 404];
        }

        if ($foto) {
            if ($user->foto) {
                $this->deleteImage($user->foto);
            }
            $user->foto = $this->uploadImage($foto, 'users');
        }

        if (isset($data['name'])) {
            $user->name = $data['name'];
        }
        if (isset($data['username'])) {
            $user->username = $data['username'];
        }
        if (isset($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();

        if (isset($data['phone'])) {
            $pemerintah->phone = $data['phone'];
        }
        if (isset($data['institusi_id'])) {
            $pemerintah->institusi_id = $data['institusi_id'];
        }
        if (isset($data['status'])) {
            $pemerintah->status = $data['status'];
        }
        $pemerintah->save();

        return [
            'message'   => 'User and Pemerintah updated successfully',
            'user'      => $user,
            'pemerintah'=> $pemerintah,
        ];
    }

    public function getPemerintahPaginated()
    {
        $pemerintah = Pemerintah::paginate(10);
        foreach ($pemerintah as $pData) {
            $pData->username      = $pData->user->username ?? null;
            $pData->name          = $pData->user->name ?? null;
            $pData->institusiName = $pData->institusi->name ?? null;
        }
        return $pemerintah;
    }

    /**
     * Retrieve details for a specific Pemerintah.
     */
    public function getPemerintahDetails($id)
    {
        $pemerintah = Pemerintah::with(['user', 'institusi'])->find($id);
        if (!$pemerintah) {
            return ['error' => 'Pemerintah not found'];
        }
        $pemerintah->user;
        $pemerintah->institusi;
        return $pemerintah;
    }

    /**
     * Search Pemerintah records by related user name/username, phone, or institusi name.
     */
    public function searchPemerintah($search)
    {
        $pemerintah = Pemerintah::with(['user', 'institusi'])
            ->when($search, function ($query) use ($search) {
                $query->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%$search%")
                              ->orWhere('username', 'like', "%$search%");
                })
                ->orWhere('phone', 'like', "%$search%")
                ->orWhereHas('institusi', function ($institusiQuery) use ($search) {
                    $institusiQuery->where('name', 'like', "%$search%");
                });
            })
            ->paginate(10);

        $pemerintah->getCollection()->transform(function ($item) {
            return [
                'id'            => $item->id,
                'status'        => $item->status,
                'phone'         => $item->phone,
                'institusi_id'  => $item->institusi_id,
                'username'      => $item->user->username ?? null,
                'name'          => $item->user->name ?? null,
                'institusiName' => $item->institusi->name ?? null,
                'user'          => $item->user,
                'institusi'     => $item->institusi,
            ];
        });

        return $pemerintah;
    }

    /**
     * Delete a Pemerintah account along with its related user record.
     */
    public function deletePemerintah($id)
    {
        $pemerintah = Pemerintah::find($id);

        if (!$pemerintah) {
            return ['error' => 'Pemerintah not found'];
        }

        $user = $pemerintah->user;
        if ($user) {
            if ($user->foto) {
                $this->deleteImage($user->foto);
            }
            $pemerintah->delete();
            $user->delete();
        }

        return ['message' => 'Pemerintah and associated user deleted successfully'];
    }

    /**
     * Find a Masyarakat by phone.
     */
    public function findMasyarakatByPhone($phone)
    {
        $masyarakat = Masyarakat::where('phone', $phone)->first();
        if (!$masyarakat) {
            return ['error' => 'Masyarakat not found'];
        }
        $user = User::find($masyarakat->id);
        return $user ? $user : ['error' => 'User for Masyarakat not found'];
    }

    /**
     * Update a user's password.
     */
    public function ubahPassword($username, $newPassword)
    {
        $user = User::where('username', $username)->first();

        if (!$user) {
            return ['error' => 'User not found'];
        }

        $user->password = Hash::make($newPassword);
        $user->save();

        return ['message' => 'Password updated successfully', 'user' => $user];
    }
}
