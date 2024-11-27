<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Berita;

class BeritaController extends Controller
{
    public function indexWeb(Request $request)
    {
        // Get berita with related kategori and user (editor)
        $berita = Berita::with(['kategori' => function($query) {
                $query->select('id', 'name'); // Hanya ambil id dan name dari kategori
            }, 'user' => function($query) {
                $query->select('id', 'name'); // Hanya ambil id dan name dari user (editor)
            }])
            ->paginate(10); // 10 items per page

        return response()->json($berita);
    }

    public function indexMobile(Request $request)
    {
        // Get berita with related kategori and user (editor)
        $berita = Berita::with(['kategori' => function($query) {
                $query->select('id', 'name'); // Hanya ambil id dan name dari kategori
            }, 'user' => function($query) {
                $query->select('id', 'name'); // Hanya ambil id dan name dari user (editor)
            }])
            ->paginate(7); // 10 items per page

        return response()->json($berita);
    }

}
