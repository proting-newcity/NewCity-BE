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
    private const ERROR_REPORT_NOT_FOUND = 'Report not found';
    private const ERROR_UNAUTHORIZED = 'You are not authorized!';
    private const RULE_REQUIRED_STRING = 'required|string';
    /**
     * Display a listing of the reports.
     */
    public function index()
    {
        $reports = Report::with('masyarakat.user:id,name')->paginate(10);
        
        $reports->transform(function ($report) {
            $report->pelapor = optional($report->masyarakat->user)->name;
            unset($report->masyarakat);
            return $report;
        });
        
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
        $validator = Validator::make($request->all(), [
            'judul' => 'required|string|max:100',
            'deskripsi' => self::RULE_REQUIRED_STRING,
            'lokasi' => self::RULE_REQUIRED_STRING,
            'foto' => 'required|image|mimes:jpeg,png,jpg,gif',
            'id_pemerintah' => 'nullable|exists:pemerintah,id',
            'id_kategori' => 'required|exists:kategori_report,id',
        ]);

        if ($validator->fails()){
            return response()->json($validator->errors(), 422);
        }
        if (!$this->checkRole('masyarakat')){
            return response()->json(['error' => self::ERROR_UNAUTHORIZED], 401);
        }

        $report = Report::create(array_merge($request->only(['judul', 'deskripsi', 'lokasi', 'id_pemerintah', 'id_kategori']), [
            'foto' => $this->uploadImage($request->file('foto'), 'reports'),
            'id_masyarakat' => auth()->id(),
            'status' => [['status' => 'Menunggu', 'deskripsi' => 'Laporan sedang diverifikasi oleh Admin', 'tanggal' => now()->toISOString()]]
        ]));

        return response()->json($report, 201);
    }

    private function filterReports($conditions)
    {
        $reports = Report::where($conditions)->paginate(10);
        return response()->json($reports->isEmpty() ? ['message' => 'No reports found'] : $reports, 200);
    }

    public function getByCategory($categoryId)
    {
        return $this->filterReports(['id_kategori' => $categoryId]);
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
     * Get Report by logged in user
     */
    public function myReports()
    {
        $user = auth('sanctum')->user();
        
        if (!$user) {
            return response()->json(['message' => self::ERROR_UNAUTHORIZED], 401);
        }
    
        $reports = Report::where('id_masyarakat', $user->id)->paginate(10);
    
        return response()->json($reports, 200);
    }
    
    /**
     * Update status.
     */
    public function addStatus(Request $request, $id)
    {
        // Validasi input
        $validated = $request->validate([
            'status' => self::RULE_REQUIRED_STRING,
        ]);

        // Ambil laporan berdasarkan ID
        $report = Report::find($id);
        if (!$report) {
            return response()->json(['message' => self::ERROR_REPORT_NOT_FOUND], 404);
        }

        if ($report->id_pemerintah == null) {
            $pemerintah = Pemerintah::inRandomOrder()->first();
            $report->id_pemerintah = $pemerintah->id;
            $report->save();
        }

        $institusiName = optional($report->pemerintah->institusi)->name ?? 'pemerintah terkait';

        // Hardcode mapping status -> deskripsi
        $statusToDescription = [
            'Dalam Proses' => "Laporan sedang ditangani oleh $institusiName.",
            'Tindak Lanjut' => "Laporan telah diproses oleh $institusiName.",
            'Selesai' => "Laporan sudah diselesaikan oleh $institusiName.",
            'Ditolak' => "Laporan tidak memenuhi syarat dan ketentuan yang berlaku.",
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

    public function show($id)
    {
        $report = Report::with(['masyarakat.user:id,name', 'pemerintah.user:id,name', 'category:id,name'])->find($id);
        if (!$report) {
            return response()->json(['message' => self::ERROR_REPORT_NOT_FOUND], 404);
        }

        return response()->json([
            'report' => $report,
            'masyarakat' => $report->masyarakat ? ['id' => $report->masyarakat->id, 'name' => $report->masyarakat->user->name] : null,
            'pemerintah' => $report->pemerintah ? ['id' => $report->pemerintah->id, 'name' => $report->pemerintah->user->name] : null,
            'kategori' => $report->category,
            'like' => RatingReport::where('id_report', $report->id)->count(),
            'comment' => Diskusi::where('id_report', $report->id)->count(),
            'hasLiked' => auth('sanctum')->check() ? auth('sanctum')->user()->toggleLikeReport($report->id, true) : false,
            'hasBookmark' => auth('sanctum')->check() ? auth('sanctum')->user()->toggleBookmark($report->id, true) : false
        ], 200);
    }

    public function searchReports(Request $request)
    {
        return $this->filterReports([['judul', 'like', "%{$request->search}%"], ['deskripsi', 'like', "%{$request->search}%"]]);
    }


    public function update(Request $request, $id)
    {
        $report = Report::find($id);
        if (!$report) {
            return response()->json(['message' => self::ERROR_REPORT_NOT_FOUND], 404);
        }
        if (!$this->checkOwner($report->masyarakat->id)){
            return response()->json(['message' => self::ERROR_UNAUTHORIZED], 401);
        }
        $data = $request->only(['judul', 'deskripsi', 'lokasi', 'status', 'id_pemerintah', 'id_kategori']);
        if ($request->hasFile('foto')) {
            $this->deleteImage($report->foto);
            $data['foto'] = $this->uploadImage($request->file('foto'), 'reports');
        }
        $report->update($data);
        return response()->json($report, 200);
    }

    /**
     * Remove the specified report from storage.
     */
    public function destroy($id)
    {
        $report = Report::find($id);
        if (!$report){
            return response()->json(['message' => self::ERROR_REPORT_NOT_FOUND], 404);
        }
        if (!$this->checkOwner($report->masyarakat->id)){
            return response()->json(['message' => self::ERROR_UNAUTHORIZED], 401);
}
        $this->deleteImage($report->foto);
        $report->delete();
        return response()->json(['message' => 'Report deleted successfully'], 200);
    }

    public function like(Request $request)
    {
        return response()->json(['success' => auth()->user()->toggleLikeReport($request->id, false)]);
    }

    public function bookmark(Request $request)
    {
        return response()->json(['success' => auth()->user()->toggleBookmark($request->id, false)]);
    }

    public function diskusiStore(Request $request, $id)
    {
        $validator = Validator::make($request->all(), ['content' => self::RULE_REQUIRED_STRING]);
        if ($validator->fails()){
            return response()->json($validator->errors(), 422);
        }
        return response()->json(['success' => auth()->user()->sendDiskusi($id, $request->content)]);
    }

    public function diskusiShow($id)
    {
        $diskusi = Diskusi::where('id_report', $id)->with('user')->get();
        return response()->json($diskusi, 200);
    }

    public function likedReports()
    {
        $likedReportIds = RatingReport::where('id_user', auth('sanctum')->id())->pluck('id_report');
        return response()->json(Report::whereIn('id', $likedReportIds)->paginate(10), 200);
    }
}
