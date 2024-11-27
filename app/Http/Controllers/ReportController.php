<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    /**
     * Display a listing of the reports.
     */
    public function index()
    {
        $reports = Report::all();
        foreach ($reports as $report) {
            $report->foto = Storage::url(str_replace('storage/', '', $report->foto));
        }
        return response()->json($reports, 200);
    }

    /**
     * Store a newly created report in storage.
     */
    public function store(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'judul' => 'required|string|max:100',
            'deskripsi' => 'required|string',
            'lokasi' => 'required|string',
            'status' => 'required|array',
            'foto' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'id_masyarakat' => 'required|exists:masyarakat,id',
            'id_pemerintah' => 'nullable|exists:pemerintah,id',
            'id_kategori' => 'nullable|exists:kategori_report,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Handle file upload
        $foto = $request->file('foto');
        $fotoPath = $foto->store('public/reports'); 

        // Create report
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
    }

    /**
    * Get all reports by category ID.
    */
    public function getByCategory($categoryId)
    {
        // Fetch reports where id_kategori matches the provided category ID
        $reports = Report::where('id_kategori', $categoryId)->get();

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

        $report->foto = Storage::url(str_replace('storage/', '', $report->foto));

        return response()->json($report, 200);
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

        // Validate request
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

        // Update fields if provided
        $report->update($request->only([
            'judul', 'deskripsi', 'lokasi', 'status', 'id_masyarakat', 'id_pemerintah', 'id_kategori'
        ]));

        if ($request->hasFile('foto')) {
            // Store the new file and get its path
            $fotoPath = $request->file('foto')->store('public/reports');
    
            // Save the new file path to the report
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
}
