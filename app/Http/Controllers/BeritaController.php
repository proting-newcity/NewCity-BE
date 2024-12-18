<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

use App\Models\Berita;

class BeritaController extends Controller
{
    public function indexWeb(Request $request)
    {
        // Get berita with related kategori and user (editor)
        $berita = Berita::with(['kategori' => function($query) {
                $query->select('id', 'name', 'foto'); // Hanya ambil id dan name dari kategori
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
                $query->select('id', 'name', 'foto'); // Hanya ambil id dan name dari kategori
            }, 'user' => function($query) {
                $query->select('id', 'name'); // Hanya ambil id dan name dari user (editor)
            }])
            ->paginate(7); // 10 items per page

        return response()->json($berita);
    }

    public function getByCategory($categoryId)
    {
        $berita = Berita::with([
            'kategori' => function($query) {
                $query->select('id', 'name', 'foto');
            },
            'user' => function($query) {
                $query->select('id', 'name');
            }
        ])
        ->where('id_kategori', $categoryId) 
        ->paginate(10);
    
    
    
        if ($berita->isEmpty()) {
            return response()->json(['message' => 'No berita found for this category'], 404);
        }
    
        // Return the paginated result
        return response()->json($berita, 200);
    }
    

    public function store(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:50',
            'content' => 'required|string',
            'foto' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'required|string|max:50',
            'id_kategori' => 'required|integer|exists:kategori_berita,id',
            'id_user' => 'required|integer|exists:user,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $foto = $request->file('foto');
        $fotoPath = str_replace('public/', 'storage/', $foto->store('public/berita'));

        // Create a new berita
        $berita = Berita::create([
            'title' => $request->title,
            'content' => $request->content,
            'foto' => $fotoPath,
            'status' => $request->status,
            'id_kategori' => $request->id_kategori,
            'id_user' => $request->id_user,
        ]);

        return response()->json($berita, 201);
    }

    /**
     * Summary of like
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function like(Request $request)
    {
        $berita = berita::find($request->id);
        $response = auth()->user()->toggleLikeBerita($berita->id);

        return response()->json(['success' => $response]);
    }
}
