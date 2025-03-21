<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;


class MasyarakatController extends Controller
{public function notification(Request $request)
    {
        $user = auth()->user();
    
        if (!$this->checkRole("masyarakat")) {
            return response()->json(['error' => 'You are not authorized!'], 401);
        }
    
        $masyarakat = $user->masyarakat;
        $diskusi = $masyarakat->diskusi ? $masyarakat->diskusi()->get()->map(function ($item) {
            return [
                'foto_profile' => $item->user->foto ?? null,
                'name' => $item->user->name ?? 'Unknown User',
                'type' => 'diskusi',
                'content' => $item->content,
                'foto' => $item->report->foto ?? null,
                'tanggal' => $item->tanggal,
                'id_report' => $item->id_report,
            ];
        }) : collect();
        
        $likes = $masyarakat->likes ? $masyarakat->likes()->get()->map(function ($item) {
            return [
                'foto_profile' => $item->user->foto ?? null,
                'name' => $item->user->name ?? 'Unknown User',
                'type' => 'like',
                'content' => 'Liked a report',
                'foto' => $item->report->foto ?? null,
                'tanggal' => $item->tanggal,
                'id_report' => $item->id_report,
            ];
        }) : collect();

        $combined = $diskusi->merge($likes)->sortByDesc('tanggal')->values();
        $perPage = 10;
        $page = $request->input('page', 1);
        $total = $combined->count();
        $paginated = new LengthAwarePaginator(
            $combined->forPage($page, $perPage)->values(),
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    
        return response()->json($paginated);
    }
}
