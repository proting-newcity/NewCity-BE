<?php

namespace App\Http\Controllers;

use App\Models\RatingBerita;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Models\Berita;

class BeritaController extends Controller
{
    public function indexWeb(Request $request)
    {
        // Get berita with related kategori and user (editor)
        $berita = Berita::with([
            'kategori' => function ($query) {
                $query->select('id', 'name', 'foto'); // Hanya ambil id dan name dari kategori
            },
            'user' => function ($query) {
                $query->select('id', 'name'); // Hanya ambil id dan name dari user (editor)
            }
        ])
            ->paginate(10); // 10 items per page

        // get like count
        $berita->getCollection()->transform(function ($item) {
            $item->like_count = RatingBerita::where('id_berita', $item->id)->count();
            if (auth('sanctum')->check()) {
                $item->hasLiked = auth('sanctum')->user()->toggleLikeBerita($item->id, true);
            } else{
                $item->hasLiked = false;
            }
            return $item;
        });

        return response()->json($berita);
    }

    public function indexMobile(Request $request)
    {
        // Get berita with related kategori and user (editor)
        $berita = Berita::with([
            'kategori' => function ($query) {
                $query->select('id', 'name', 'foto'); // Hanya ambil id dan name dari kategori
            },
            'user' => function ($query) {
                $query->select('id', 'name'); // Hanya ambil id dan name dari user (editor)
            },
        ])
            ->paginate(7); // 10 items per page

        // get like count
        $berita->getCollection()->transform(function ($item) {
            $item->like_count = RatingBerita::where('id_berita', $item->id)->count();
            return $item;
        });

        return response()->json($berita);
    }

    public function getByCategory($categoryId)
    {
        $berita = Berita::with([
            'kategori' => function ($query) {
                $query->select('id', 'name', 'foto');
            },
            'user' => function ($query) {
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
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:50',
            'content' => 'required|string',
            'foto' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'required|string|max:50',
            'id_kategori' => 'required|integer|exists:kategori_berita,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if (!$this->checkRole("admin")) {
            return response()->json(['error' => 'You are not authorized!'], 401);
        }

        $fotoPath = $this->uploadImage($request->file('foto'), 'public/berita');

        $berita = Berita::create([
            'title' => $request->title,
            'content' => $request->content,
            'foto' => $fotoPath,
            'status' => $request->status,
            'id_kategori' => $request->id_kategori,
            'id_user' => auth()->user()->id,
        ]);

        return response()->json($berita, 201);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:100',
            'content' => 'nullable|string',
            'status' => 'required|string|max:50',
            'id_kategori' => 'required|integer|exists:kategori_berita,id',
        ]);

        $berita = Berita::find($id);
        if (!$berita) {
            return response()->json(['message' => 'Berita not found'], 404);
        }

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (!$this->checkOwner($berita->admin->id)) {
            return response()->json(['message' => 'You are not authorized!'], 401);
        }

        $berita->update($request->only([
            'title',
            'content',
            'lokasi',
            'status',
            'id_kategori'
        ]));

        if ($request->hasFile('foto')) {
            if ($berita->foto) {
                $this->deleteImage($berita->foto);
            }

            $berita->foto = $this->uploadImage($request->file('foto'), 'public/berita');;
        }
        $berita->save();

        return response()->json($berita, 200);
    }

    public function destroy($id)
    {
        $berita = Berita::find($id);

        if (!$berita) {
            return response()->json(['message' => 'Berita not found'], 404);
        }

        if($berita->foto){
            $this->deleteImage($berita->foto);
        }

        $berita->delete();

        return response()->json(['message' => 'Berita deleted successfully'], 200);
    }

    public function searchBerita(Request $request)
    {

        $validated = $request->validate([
            'search' => 'required|string|max:255',
        ]);

        $search = $validated['search'];

        $reports = Berita::where('title', 'like', "%$search%")
            ->orWhere('content', 'like', "%$search%")
            ->orWhere('status', 'like', "%$search%")->with([
                'kategori' => function ($query) {
                    $query->select('id', 'name', 'foto');
                },
                'user' => function ($query) {
                    $query->select('id', 'name');
                }
            ])
            ->paginate(10);

        if ($reports->isEmpty()) {
            return response()->json(['message' => 'No reports found'], 404);
        }

        return response()->json($reports, 200);
    }

    /**
     * Summary of like
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function like(Request $request)
    {
        $berita = berita::find($request->id);
        $response = auth()->user()->toggleLikeBerita($berita->id, false);

        return response()->json(['success' => $response]);
    }
}
