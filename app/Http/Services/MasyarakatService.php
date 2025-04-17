<?php
namespace App\Services;

use App\Models\Masyarakat;
use App\Http\Resources\Masyarakat\NotificationResource;
use Illuminate\Pagination\LengthAwarePaginator;

class MasyarakatService
{
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

    protected function mapItem($item, string $type): array
    {
        return [
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
