<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    /**
     * Display a listing of the reports.
     */
    public function index()
    {
        $reports = Report::paginate(10);
        return response()->json($reports, 200);
    }

    /**
     * Store a newly created report in storage.
     */
    public function store(Request $request)
{
    try {
        $validator = Validator::make($request->all(), [
            'judul' => 'required|string|max:100',
            'deskripsi' => 'required|string',
            'lokasi' => 'required|string',
            'status' => 'required|array',
            'foto' => 'required|image|mimes:jpeg,png,jpg,gif',
            'id_masyarakat' => 'required|exists:masyarakat,id',
            'id_pemerintah' => 'nullable|exists:pemerintah,id',
            'id_kategori' => 'required|exists:kategori_report,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $foto = $request->file('foto');
        $fotoPath = str_replace('public/', 'storage/', $foto->store('public/reports'));

        $report = Report::create([
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'lokasi' => $request->lokasi,
            'status' => $request->status,
            'foto' => $fotoPath,
            'id_masyarakat' => $request->id_masyarakat,
            'id_pemerintah' => $request->id_pemerintah,
            'id_kategori' => $request->id_kategori,
        ]);

        return response()->json($report, 201);
    } catch (\Exception $e) {
        Log::error('Error creating report: ' . $e->getMessage());

        return response()->json([
            'error' => 'An error occurred while creating the report. Please try again later.'
        ], 500);
    }
}

    /**
    * Get all reports by category ID.
    */
    public function getByCategory($categoryId)
    {
        $reports = Report::where('id_kategori', $categoryId)->paginate(10);

        if ($reports->isEmpty()) {
            return response()->json(['message' => 'No reports found for this category'], 404);
        }

        return response()->json($reports, 200);
    }


    /**
     * Display the specified report.
     */
    public function show($id)
    {
        $report = Report::find($id);
        if (!$report) {
            return response()->json(['message' => 'Report not found'], 404);
        }

        return response()->json([
            'report' => $report,
            'masyarakat' => [
                'id' => $report->masyarakat->id,
                'name' => $report->masyarakat->user->name,
            ], 'pemerintah' => [
                'id' => $report->pemerintah->id ?? null,
                'name' => $report->pemerintah->user->name ?? null,
            ], 'kategori' => [
                'id' => $report->category->id ?? null,
                'name' => $report->category->name ?? null,
            ]
        ], 200);
    }

    public function searchReports(Request $request)
    {
        $validated = $request->validate([
            'search' => 'required|string|max:255',
        ]);
    
        $search = $validated['search'];
    
        $reports = Report::where('judul', 'like', "%$search%")
                         ->orWhere('deskripsi', 'like', "%$search%")
                         ->paginate(10);
    

        if ($reports->isEmpty()) {
            return response()->json(['message' => 'No reports found'], 404);
        }
    
        return response()->json($reports, 200);
    }
    

    /**
     * Update the specified report in storage.
     */
    public function update(Request $request, $id)
    {
        $report = Report::find($id);
        if (!$report) {
            return response()->json(['message' => 'Report not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'judul' => 'string|max:100',
            'deskripsi' => 'string',
            'lokasi' => 'string',
            'status' => 'array',
            'foto' => 'required|file|mimes:jpeg,png,jpg,gif|max:2048',
            'id_masyarakat' => 'exists:masyarakat,id',
            'id_pemerintah' => 'exists:pemerintah,id',
            'id_kategori' => 'exists:kategori_report,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $report->update($request->only([
            'judul', 'deskripsi', 'lokasi', 'status', 'id_masyarakat', 'id_pemerintah', 'id_kategori'
        ]));

        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('public/reports');
    
            $report->foto = $fotoPath;
            $report->save();
        }

        return response()->json($report, 200);
    }

    /**
     * Remove the specified report from storage.
     */
    public function destroy($id)
    {
        $report = Report::find($id);
        if (!$report) {
            return response()->json(['message' => 'Report not found'], 404);
        }

        $report->delete();

        return response()->json(['message' => 'Report deleted successfully'], 200);
    }

    /**
     * Summary of like
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function like(Request $request)
    {
        $report = Report::find($request->id);
        $response = auth()->user()->toggleLikeReport($report->id);

        return response()->json(['success' => $response]);
    }
}
