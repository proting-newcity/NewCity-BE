<?php

namespace App\Http\Controllers;

use App\Http\Services\ReportService;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReportController extends Controller
{
    private const ERROR_REPORT_NOT_FOUND = 'Report not found';
    private const ERROR_UNAUTHORIZED = 'You are not authorized!';
    private const RULE_REQUIRED_STRING = 'required|string';

    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }
    
    /**
     * Display paginated reports
     */
    public function index()
    {
        return response()->json($this->reportService->getPaginatedReports(), 200);
    }

    /**
     * Display reports which status equal to null, 'Menunggu', 'Rejected'.
     */
    public function indexAdmin()
    {
        $reports = $this->reportService->getReportsForAdmin();

        if (empty($reports)) {
            return response()->json(['message' => 'No reports found for this status'], 404);
        }
        return response()->json($reports, 200);
    }

    /**
     * Store a newly created report.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'judul'        => 'required|string|max:100',
            'deskripsi'    => 'required|string',
            'lokasi'       => 'required|string',
            'foto'         => 'required|image|mimes:jpeg,png,jpg,gif',
            'id_pemerintah'=> 'nullable|exists:pemerintah,id',
            'id_kategori'  => 'required|exists:kategori_report,id',
        ]);
        if ($validator->fails()){
            return response()->json($validator->errors(), 422);
        }
        
        if (!$this->reportService->checkUserRole('masyarakat')) {
            return response()->json(['error' => 'You are not authorized!'], 401);
        }

        $report = $this->reportService->createReport($request->all(), $request->file('foto'));

        return response()->json($report, 201);
    }

    /**
     * Get reports by category.
     */
    public function getByCategory($categoryId)
    {
        return response()->json($this->reportService->getReportsByCondition(['id_kategori' => $categoryId]), 200);
    }

    /**
     * Get all reports by status.
     */
    public function getReportsByStatus($status)
    {
        $reports = $this->reportService->getReportsByStatus($status);
        if (empty($reports)) {
            return response()->json(['message' => 'No reports found for this status'], 404);
        }
        return response()->json(['data' => $reports], 200);
    }

    /**
     * Get Report by logged in user
     */
    public function myReports()
    {
        $reportData = $this->reportService->getMyReports();
        if (isset($reportData['error'])) {
            return response()->json($reportData, 401);
        }
        return response()->json($reportData, 200);
    }
    
    /**
     * Update status.
     */
    public function addStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|string',
        ]);
        $result = $this->reportService->addStatus($id, $validated['status']);
        return response()->json($result, isset($result['error']) ? 404 : 200);
    }

    /**
     * Show report details.
     */
    public function show($id)
    {
        $report = $this->reportService->getReportDetails($id);
        if (isset($report['error'])) {
            return response()->json(['message' => 'Report not found'], 404);
        }
        return response()->json($report, 200);
    }

    /**
     * Search report by keyword.
     */
    public function searchReports(Request $request)
    {
        $searchTerm = $request->input('search');
        return response()->json($this->reportService->searchReports($searchTerm), 200);
    }


    /**
     * Update a report.
     */
    public function update(Request $request, $id)
    {
        $report = $this->reportService->updateReport($id, $request->all(), $request->file('foto'));
        if (isset($report['error'])) {
            return response()->json($report, $report['error_code'] ?? 400);
        }
        return response()->json($report, 200);
    }

    /**
     * Delete report.
     */
    public function destroy($id)
    {
        $result = $this->reportService->deleteReport($id);
        $code = isset($result['error']) ? 404 : 200;
        return response()->json($result, $code);
    }

    /**
     * Toggle Like on a report.
     */
    public function like(Request $request)
    {
        $result = auth()->user()->toggleLikeReport($request->id, false);
        return response()->json(['success' => $result], 200);
    }

    /**
     * Toggle bookmark on a report.
     */
    public function bookmark(Request $request)
    {
        $result = auth()->user()->toggleBookmark($request->id, false);
        return response()->json(['success' => $result], 200);
    }

    /**
     * Store a new discussion message for a report.
     */
    public function diskusiStore(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
        ]);
        if ($validator->fails()){
            return response()->json($validator->errors(), 422);
        }
        $success = auth()->user()->sendDiskusi($id, $request->content);
        return response()->json(['success' => $success], 200);
    }

    /**
     * Show all discussion messages for a report.
     */
    public function diskusiShow($id)
    {
        return response()->json($this->reportService->getDiskusiForReport($id), 200);
    }

    /**
     * Get liked reports.
     */
    public function likedReports()
    {
        return response()->json($this->reportService->getLikedReports(), 200);
    }
}