<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Services\ReportService;
use App\Http\Requests\Report\StoreReportRequest;
use App\Http\Requests\Report\UpdateReportRequest;
use App\Http\Requests\Report\AddStatusRequest;
use App\Http\Requests\Report\DiskusiStoreRequest;
use App\Http\Requests\Report\BookmarkRequest;
use App\Http\Traits\ApiResponseTrait;

class ReportController extends Controller
{
    use ApiResponseTrait;

    public function __construct(protected ReportService $reportService)
    {
    }

    /**
     * Display paginated reports
     */
    public function index()
    {
        $data = $this->reportService->getPaginatedReports();
        return $this->success($data);
    }

    /**
     * Display reports which status equal to null, 'Menunggu', 'Rejected'.
     */
    public function indexAdmin()
    {
        $reports = $this->reportService->getReportsForAdmin();
        if (empty($reports)) {
            return $this->error('No reports found for this status', 404);
        }
        return $this->success($reports);
    }

    /**
     * Store a newly created report.
     */
    public function store(StoreReportRequest $request)
    {
        $report = $this->reportService->createReport(
            $request->validated(),
            $request->file('foto')
        );
        return $this->success($report, 201);
    }

    /**
     * Get reports by category.
     */
    public function getByCategory($categoryId)
    {
        $data = $this->reportService->getReportsByCondition(['id_kategori' => $categoryId]);
        return $this->success($data);
    }

    /**
     * Get all reports by status.
     */
    public function getReportsByStatus($status)
    {
        $reports = $this->reportService->getReportsByStatus($status);
        if (empty($reports)) {
            return $this->error('No reports found for this status', 404);
        }
        return $this->success(['data' => $reports]);
    }

    /**
     * Get Report by logged in user
     */
    public function myReports()
    {
        $result = $this->reportService->getMyReports();
        return isset($result['error'])
            ? $this->error($result['error'], 401)
            : $this->success($result);
    }

    /**
     * Update status.
     */
    public function addStatus(AddStatusRequest $request, $id)
    {
        $result = $this->reportService->addStatus($id, $request->validated()['status']);
        return isset($result['error'])
            ? $this->error($result['error'], 404)
            : $this->success($result);
    }

    /**
     * Show report details.
     */
    public function show($id)
    {
        $report = $this->reportService->getReportDetails($id);
        return isset($report['error'])
            ? $this->error('Report not found', 404)
            : $this->success($report);
    }

    /**
     * Search report by keyword.
     */
    public function searchReports(Request $request)
    {
        $data = $this->reportService->searchReports($request->input('search'));
        return $this->success($data);
    }

    /**
     * Update a report.
     */
    public function update(UpdateReportRequest $request, $id)
    {
        $report = $this->reportService->updateReport($id, $request->validated(), $request->file('foto'));
        return isset($report['error'])
            ? $this->error($report['error'], $report['error_code'] ?? 400)
            : $this->success($report);
    }

    /**
     * Delete report.
     */
    public function destroy($id)
    {
        $result = $this->reportService->deleteReport($id);
        return isset($result['error'])
            ? $this->error($result['error'], 404)
            : $this->success($result);
    }

    /**
     * Toggle Like on a report.
     */
    public function like(Request $request)
    {
        $result = auth()->user()->toggleLikeReport($request->id, false);
        return $this->success(['success' => $result]);
    }

    /**
     * Toggle bookmark on a report.
     */
    public function bookmarkStore(Request $request)
    {
        $result = auth()->user()->toggleBookmark($request->id, false);
        return $this->success(['success' => $result]);
    }

    /**
     * Show all bookmark for a user.
     */
    public function bookmarkIndex(BookmarkRequest $request)
    {
        $masyarakat = $request->user()->masyarakat;
        $data = $this->reportService->geBookmarkReports($masyarakat);
        return $this->success($data);
    }

    /**
     * Store a new discussion message for a report.
     */
    public function diskusiStore(DiskusiStoreRequest $request, $id)
    {
        $result = auth()->user()->sendDiskusi($id, $request->validated()['content']);
        return $this->success(['success' => $result]);
    }

    /**
     * Show all discussion messages for a report.
     */
    public function diskusiShow(int $id)
    {
        $data = $this->reportService->getDiskusiForReport($id);
        return $this->success($data);
    }

    /**
     * Get liked reports.
     */
    public function likedReports()
    {
        $data = $this->reportService->getLikedReports();
        return $this->success($data);
    }
}
