<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Models\Berita;
use App\Http\Services\BeritaService;

class BeritaController extends Controller
{
    protected $beritaService;

    public function __construct(BeritaService $beritaService)
    {
        $this->beritaService = $beritaService;
    }

    /**
     * Display a paginated list of Berita.
     */
    public function index()
    {
        $berita = $this->beritaService->getPaginatedBerita();
        return response()->json($berita, 200);
    }

    /**
     * Display Berita filtered by category.
     */
    public function getByCategory($categoryId)
    {
        $berita = $this->beritaService->getBeritaByCategory($categoryId);
        if (empty($berita['data'])) {
            return response()->json(['message' => 'No berita found for this category'], 404);
        }
        return response()->json($berita, 200);
    }

    /**
     * Create a new Berita entry.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title'      => 'required|string|max:50',
            'content'    => 'required|string',
            'foto'       => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status'     => 'required|string|max:50',
            'id_kategori'=> 'required|integer|exists:kategori_berita,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if (!$this->checkRole("admin")) {
            return response()->json(['error' => 'You are not authorized!'], 401);
        }

        $berita = $this->beritaService->createBerita($request->all(), $request->file('foto'));
        return response()->json($berita, 201);
    }

    /**
     * Update an existing Berita entry.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title'      => 'nullable|string|max:100',
            'content'    => 'nullable|string',
            'status'     => 'nullable|string|max:50',
            'id_kategori'=> 'required|integer|exists:kategori_berita,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $result = $this->beritaService->updateBerita($id, $request->all(), $request->file('foto'));
        $httpCode = isset($result['error']) ? $result['error_code'] : 200;
        return response()->json($result, $httpCode);
    }

    /**
     * Delete a Berita entry.
     */
    public function destroy($id)
    {
        $result = $this->beritaService->deleteBerita($id);
        $httpCode = isset($result['error']) ? 404 : 200;
        return response()->json($result, $httpCode);
    }

    /**
     * Search for Berita by title, content, or status.
     */
    public function searchBerita(Request $request)
    {
        $search = $request->input('search');
        $result = $this->beritaService->searchBerita($search);
        if (empty($result['data'])) {
            return response()->json(['message' => 'No reports found'], 404);
        }
        return response()->json($result, 200);
    }

    /**
     * Toggle like for a Berita entry.
     */
    public function like(Request $request)
    {
        $result = $this->beritaService->toggleLikeBerita($request->id);
        return response()->json(['success' => $result], 200);
    }
}
