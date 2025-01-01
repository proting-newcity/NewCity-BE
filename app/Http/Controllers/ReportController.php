<?php

namespace App\Http\Controllers;

use App\Models\Pemerintah;
use App\Models\RatingReport;
use App\Models\Diskusi;
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
     * Display reports which status equal to null, 'Menunggu', 'Rejected'.
     */
    public function indexAdmin()
    {
        $reports = Report::all();

        // Filter laporan berdasarkan status terakhir
        $filteredReports = $reports->filter(function ($report) {
            $statuses = $report->status; // Langsung akses array
            if (is_array($statuses) && !empty($statuses)) {
                $lastStatus = end($statuses); // Ambil status terakhir
                return isset($lastStatus['status']) && $lastStatus['status'] == 'Menunggu' || $lastStatus['status'] == 'Dalam Proses' || $lastStatus['status'] == 'Ditolak';
            }
            return false;
        });
        // Jika tidak ada laporan yang cocok, kembalikan pesan error
        if ($filteredReports->isEmpty()) {
            return response()->json(['message' => 'No reports found for this status'], 404);
        }

        // Ubah hasil menjadi array tanpa key indeks
        $filteredReports = array_values($filteredReports->toArray());

        return response()->json($filteredReports, 200);
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
                'foto' => 'required|image|mimes:jpeg,png,jpg,gif',
                'id_pemerintah' => 'nullable|exists:pemerintah,id',
                'id_kategori' => 'required|exists:kategori_report,id',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            if (!$this->checkRole("masyarakat")) {
                return response()->json(['error' => 'You are not authorized!'], 401);
            }

            $fotoPath = $this->uploadImage($request->file('foto'), 'public/reports');

            $status = [
                'status' => 'Menunggu',
                'deskripsi' => 'Laporan sedang diverifikasi oleh Admin',
                'tanggal' => now()->toISOString(),
            ];

            $report = Report::create([
                'judul' => $request->judul,
                'deskripsi' => $request->deskripsi,
                'lokasi' => $request->lokasi,
                'status' => $status,
                'foto' => $fotoPath,
                'id_masyarakat' => auth()->user()->id,
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
     * Get all reports by status.
     */
    public function getReportsByStatus($status)
    {
        // Ambil semua laporan
        $reports = Report::all();

        // Filter laporan berdasarkan status terakhir
        $filteredReports = $reports->filter(function ($report) use ($status) {
            $statuses = $report->status; // Langsung akses array
            if (is_array($statuses) && !empty($statuses)) {
                $lastStatus = end($statuses); // Ambil status terakhir
                return isset($lastStatus['status']) && $lastStatus['status'] == $status;
            }
            return false;
        });

        // Jika tidak ada laporan yang cocok, kembalikan pesan error
        if ($filteredReports->isEmpty()) {
            return response()->json(['message' => 'No reports found for this status'], 404);
        }

        // Ubah hasil menjadi array tanpa key indeks
        $filteredReports = array_values($filteredReports->toArray());

        // Kembalikan laporan yang difilter
        return response()->json(['data' => $filteredReports], 200);
    }


    /**
     * Update status.
     */
    public function addStatus(Request $request, $id)
    {
        // Validasi input
        $validated = $request->validate([
            'status' => 'required|string',
        ]);

        // Ambil laporan berdasarkan ID
        $report = Report::find($id);
        if (!$report) {
            return response()->json(['error' => 'Report not found'], 404);
        }

        if ($report->id_pemerintah == null) {
            $pemerintah = Pemerintah::inRandomOrder()->first();
            $report->id_pemerintah = $pemerintah->id;
            $report->save();
        }

        // Hardcode mapping status -> deskripsi
        $statusToDescription = [
            'Dalam Proses' => 'Laporan sedang ditangani oleh ' . $report->pemerintah->institusi->name ?? '' . '.',
            'Tindak Lanjut' => 'Laporan telah diproses oleh ' . $report->pemerintah->institusi->name ?? '' . '.',
            'Selesai' => 'Laporan sudah diselesaikan oleh ' . $report->pemerintah->institusi->name ?? '' . '.',
            'Ditolak' => 'Laporan tidak memenuhi syarat dan ketentuan yang berlaku.',
        ];

        // Ambil deskripsi berdasarkan status
        $deskripsi = $statusToDescription[$validated['status']] ?? 'Status tidak diketahui';

        // Tambahkan status baru
        $newStatus = [
            'status' => $validated['status'],
            'deskripsi' => $deskripsi,
            'tanggal' => now()->toISOString(),
        ];

        // Tambahkan status ke field "status" (assume it's stored as JSON)
        $status = $report->status; // Mengambil field JSON
        $status[] = $newStatus;    // Menambahkan data baru
        $report->status = $status; // Update field JSON

        // Simpan laporan
        $report->save();

        return response()->json($report, 200);
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

        $responseData = [
            'report' => $report,
            'masyarakat' => [
                'id' => $report->masyarakat->id,
                'name' => $report->masyarakat->user->name,
            ],
            'pemerintah' => [
                'id' => $report->pemerintah->id ?? null,
                'name' => $report->pemerintah->user->name ?? null,
            ],
            'kategori' => [
                'id' => $report->category->id ?? null,
                'name' => $report->category->name ?? null,
            ],
            'like' => RatingReport::where('id_report', $report->id)->count(),
            'comment' => Diskusi::where('id_report', $report->id)->count(),
        ];

        if (auth('sanctum')->check()) {
            $responseData['hasLiked'] = auth('sanctum')->user()->toggleLikeReport($report->id, true);
            $responseData['hasBookmark'] = auth('sanctum')->user()->toggleBookmark($report->id, true);
        } else {
            $responseData['hasLiked'] = false;
        }

        return response()->json($responseData, 200);
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


    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'judul' => 'nullable|string|max:100',
            'deskripsi' => 'nullable|string',
            'lokasi' => 'nullable|string',
            'status' => 'nullable|array',
            'foto' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048',
            'id_pemerintah' => 'nullable|exists:pemerintah,id',
            'id_kategori' => 'nullable|exists:kategori_report,id',
        ]);

        $report = Report::find($id);
        if (!$report) {
            return response()->json(['message' => 'Report not found'], 404);
        }


        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (!$this->checkOwner($report->masyarakat->id)) {
            return response()->json(['message' => 'You are not authorized!'], 401);
        }

        $report->update($request->only([
            'judul',
            'deskripsi',
            'lokasi',
            'status',
            'id_pemerintah',
            'id_kategori'
        ]));

        if ($request->hasFile('foto')) {
            if ($report->foto) {
                $this->deleteImage($report->foto);
            }
            $report->foto = $this->uploadImage($request->file('foto'), 'public/report');
        }
        $report->save();

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

        if (!$this->checkOwner($report->masyarakat->id)) {
            return response()->json(['message' => 'You are not authorized!'], 401);
        }

        if ($report->foto) {
            $this->deleteImage($report->foto);
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
        $response = auth()->user()->toggleLikeReport($report->id, false);

        return response()->json(['success' => $response]);
    }

    public function bookmark(Request $request)
    {
        $report = Report::find($request->id);
        $response = auth()->user()->toggleBookmark($report->id, false);

        return response()->json(['success' => $response]);
    }

    public function diskusiStore(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $report = Report::find($id);
        $response = auth()->user()->sendDiskusi($report->id, $request->content);

        return response()->json(['success' => $response]);
    }

    public function diskusiShow($id)
    {

        $report = Report::find($id);
        $diskusi = Diskusi::where('id_report', $report->id)->get();
        foreach ($diskusi as $data) {
            $data->user;
        }
        $responseData = $diskusi;

        return response()->json($responseData, 200);
    }

}