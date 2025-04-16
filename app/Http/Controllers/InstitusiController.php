<?php

namespace App\Http\Controllers;


use App\Http\Services\InstitusiService;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InstitusiController extends Controller
{
    protected $institusiService;
    private const INSTITUSI_NOT_FOUND = 'Institusi not found';

    public function __construct(InstitusiService $institusiService)
    {
        $this->institusiService = $institusiService;
    }

    /**
     * Display a listing of all institusi.
     */
    public function index()
    {
        $institusis = $this->institusiService->getAll();
        return response()->json($institusis, 200);
    }

    /**
     * Display the specified institusi.
     */
    public function show($id)
    {
        $institusi = $this->institusiService->findById($id);
        if (!$institusi) {
            return response()->json(['message' => self::INSTITUSI_NOT_FOUND], 404);
        }

        return response()->json($institusi, 200);
    }

    /**
     * Store a newly created institusi.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $institusi = $this->institusiService->create($validatedData);
        return response()->json($institusi, 201);
    }

    /**
     * Update the specified institusi.
     */
    public function update(Request $request, $id)
    {
        $institusi = $this->institusiService->findById($id);
        if (!$institusi) {
            return response()->json(['message' => self::INSTITUSI_NOT_FOUND], 404);
        }

        $validatedData = $request->validate([
            'name' => 'nullable|string|max:255',
        ]);

        $institusi = $this->institusiService->update($id, $validatedData);
        return response()->json($institusi, 200);
    }

    /**
     * Remove the specified institusi.
     */
    public function destroy($id)
    {
        $institusi = $this->institusiService->findById($id);
        if (!$institusi) {
            return response()->json(['message' => self::INSTITUSI_NOT_FOUND], 404);
        }

        $this->institusiService->delete($id);
        return response()->json(['message' => 'Institusi deleted successfully'], 200);
    }
}
