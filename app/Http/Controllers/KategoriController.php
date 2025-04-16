<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Http\Services\KategoriService;

class KategoriController extends Controller
{
    protected $kategoriService;

    public function __construct(KategoriService $kategoriService)
    {
        $this->kategoriService = $kategoriService;
    }

    /**
     * Display a listing of the resource.
     */
    public function indexReport()
    {
        $kategoriReports = $this->kategoriService->getAllReportCategories();
        return response()->json($kategoriReports, 200);
    }

    /**
     * Display a listing of berita categories.
     */
    public function indexBerita()
    {
        $kategoriBerita = $this->kategoriService->getAllBeritaCategories();
        return response()->json($kategoriBerita, 200);
    }

    /**
     * Store a newly created report category.
     */
    public function storeReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (!$this->checkRole("admin")) {
            return response()->json(['error' => 'You are not authorized!'], 401);
        }

        $kategoriReport = $this->kategoriService->createReportCategory([
            'name' => $request->name,
        ]);

        return response()->json($kategoriReport, 201);
    }

    public function storeBerita(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'foto' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (!$this->checkRole("admin")) {
            return response()->json(['error' => 'You are not authorized!'], 401);
        }

        $kategoriBerita = $this->kategoriService->createBeritaCategory([
            'name' => $request->name,
        ], $request->file('foto'));

        return response()->json($kategoriBerita, 201);
    }
}
