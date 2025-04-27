<?php
namespace App\Http\Services;

use App\Models\User;
use App\Models\Masyarakat;
use App\Http\Resources\Masyarakat\NotificationResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use App\Http\Traits\ImageUploadTrait;
use Illuminate\Support\Facades\Auth;


class MasyarakatService
{
    use ImageUploadTrait;

    /**
     * Update an existing Masyarakat account.
     */
    public function updateMasyarakat(array $data, $foto = null)
    {
        $user = Auth::user();
        $masyarakat = Masyarakat::find(Auth::user()->id);

        if (!$user || !$masyarakat) {
            return ['error' => 'User or Masyarakat not found', 'error_code' => 404];
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
            $masyarakat->phone = $data['username'];
        }
        if (isset($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();
        $masyarakat->save();

        return [
            'message'   => 'User and Masyarakat updated successfully',
            'user'      => $user,
            'masyarakat'=> $masyarakat,
        ];
    }
    public function getNotifications(Masyarakat $masyarakat, int $perPage = 10, int $page = 1): LengthAwarePaginator
    {
        // get diskusi
        $diskusi = $masyarakat->diskusi()
            ->get()
            ->map(fn($item) => $this->mapItem($item, 'diskusi'));

        // get likes
        $likes = $masyarakat->likes()
            ->get()
            ->map(fn($item) => $this->mapItem($item, 'like'));

        // merge, sort, and paginate
        $combined = $diskusi->merge($likes)
            ->sortByDesc('tanggal')
            ->values();

        $total = $combined->count();
        $slice = $combined->forPage($page, $perPage)->values();

        return new LengthAwarePaginator(
            NotificationResource::collection($slice),
            $total,
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );
    }

    protected function mapItem($item, string $type): object
    {
        return (object) [
            'foto_profile' => $item->user->foto ?? null,
            'name' => $item->user->name ?? 'Unknown User',
            'type' => $type,
            'content' => $type === 'diskusi'
                ? $item->content
                : 'Liked a report',
            'foto' => $item->report->foto ?? null,
            'tanggal' => $item->tanggal,
            'id_report' => $item->id_report,
        ];
    }
}
