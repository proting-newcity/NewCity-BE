<?php

namespace App\Http\Services;

use App\Models\Pemerintah;
use App\Models\RatingReport;
use App\Models\Diskusi;
use App\Models\Report;
use Illuminate\Support\Facades\Auth;
use App\Http\Traits\ImageUploadTrait;
class ReportService
{
    use ImageUploadTrait;

    private const MSG_NOT_FOUND = 'Report not found';
    private const MSG_UNAUTHORIZED = 'You are not authorized!';
    /**
     * Retrieve paginated reports with related masyarakat user.
     */
    public function getPaginatedReports()
    {
        $reports = Report::with('masyarakat.user:id,name')->paginate(10);

        $reports->getCollection()->transform(function ($report) {
            $statusHistory = $report->status;
            $latestStatus = end($statusHistory);
            if (in_array($latestStatus['status'], ['Ditolak', 'Menunggu'])) {
                return null;
            }
            $report->pelapor = optional($report->masyarakat->user)->name;
            unset($report->masyarakat);

            return $report;
        });
        $reports->setCollection($reports->getCollection()->filter());

        return $reports;
    }

    /**
     * Get reports for admin based on certain statuses.
     */
    public function getReportsForAdmin()
    {
        $reports = Report::all()->filter(function ($report) {
            $statuses = $report->status;
            if (is_array($statuses) && !empty($statuses)) {
                $lastStatus = end($statuses);
                return isset($lastStatus['status']) && in_array($lastStatus['status'], ['Menunggu', 'Dalam Proses', 'Ditolak']);
            }
            return false;
        });
        return array_values($reports->toArray());
    }

    /**
     * Create a new report.
     */
    public function createReport(array $data, $foto)
    {
        $uploadedPhoto = $this->uploadImage($foto, 'reports');

        $reportData = array_merge(
            [
                'foto' => $uploadedPhoto,
                'id_masyarakat' => Auth::id(),
                'status' => [
                    [
                        'status' => 'Menunggu',
                        'deskripsi' => 'Laporan sedang diverifikasi oleh Admin',
                        'tanggal' => now()->toISOString()
                    ]
                ]
            ],
            array_intersect_key($data, array_flip(['judul', 'deskripsi', 'lokasi', 'id_pemerintah', 'id_kategori']))
        );
        return Report::create($reportData);
    }

    /**
     * Retrieve reports by arbitrary condition.
     */
    public function getReportsByCondition(array $conditions)
    {
        $reports = Report::where($conditions)->paginate(10);
        if ($reports->isEmpty()) {
            return ['message' => 'No reports found'];
        }
        return $reports;
    }

    /**
     * Filter and return reports by their last status value.
     */
    public function getReportsByStatus(string $status)
    {
        $reports = Report::all()->filter(function ($report) use ($status) {
            $statuses = $report->status;
            if (is_array($statuses) && !empty($statuses)) {
                $lastStatus = end($statuses);
                return isset($lastStatus['status']) && $lastStatus['status'] === $status;
            }
            return false;
        });
        return array_values($reports->toArray());
    }

    /**
     * Get reports created by the current authenticated user.
     */
    public function getMyReports()
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            return ['error' => self::MSG_UNAUTHORIZED];
        }
        return Report::where('id_masyarakat', $user->id)->paginate(10);
    }

    /**
     * Add a new status entry to the report identified by id.
     */
    public function addStatus(int $reportId, string $newStatus)
    {
        $report = Report::find($reportId);
        if (!$report) {
            return ['error' => self::MSG_NOT_FOUND];
        }

        if (is_null($report->id_pemerintah)) {
            $pemerintah = Pemerintah::inRandomOrder()->first();
            $report->id_pemerintah = $pemerintah->id;
            $report->save();
        }

        $institusiName = optional($report->pemerintah->institusi)->name ?? 'pemerintah terkait';
        $statusMapping = [
            'Dalam Proses' => "Laporan sedang ditangani oleh $institusiName.",
            'Tindak Lanjut' => "Laporan telah diproses oleh $institusiName.",
            'Selesai' => "Laporan sudah diselesaikan oleh $institusiName.",
            'Ditolak' => "Laporan tidak memenuhi syarat dan ketentuan yang berlaku.",
        ];
        $description = $statusMapping[$newStatus] ?? 'Status tidak diketahui';

        $newStatusEntry = [
            'status' => $newStatus,
            'deskripsi' => $description,
            'tanggal' => now()->toISOString(),
        ];

        $statuses = collect($report->status ?? []);

        $isDuplicate = $statuses->contains(function ($item) use ($newStatusEntry) {
            return $item['status'] === $newStatusEntry['status']
                && $item['deskripsi'] === $newStatusEntry['deskripsi'];
        });

        if (!$isDuplicate) {
            $statuses->push($newStatusEntry);
            $report->status = $statuses->all();
            $report->save();
        }

        return $report;
    }

    /**
     * Retrieve detailed report information.
     */
    public function getReportDetails($id)
    {
        $report = Report::with(['masyarakat.user:id,name', 'pemerintah.user:id,name', 'category:id,name'])->find($id);
        if (!$report) {
            return ['error' => self::MSG_NOT_FOUND];
        }

        $report->status = collect($report->status)
            ->unique(function ($item) {
                return $item['status'] . '|' . $item['deskripsi'];
            })
            ->values()
            ->all();

        return [
            'report' => $report,
            'masyarakat' => $report->masyarakat ? ['id' => $report->masyarakat->id, 'name' => $report->masyarakat->user->name] : null,
            'pemerintah' => $report->pemerintah ? ['id' => $report->pemerintah->id, 'name' => $report->pemerintah->user->name] : null,
            'kategori' => $report->category,
            'like' => RatingReport::where('id_report', $report->id)->count(),
            'comment' => Diskusi::where('id_report', $report->id)->count(),
            'hasLiked' => auth('sanctum')->check() ? auth('sanctum')->user()->toggleLikeReport($report->id, true) : false,
            'hasBookmark' => auth('sanctum')->check() ? auth('sanctum')->user()->toggleBookmark($report->id, true) : false,
        ];
    }

    /**
     * Search reports by title and description.
     */
    public function searchReports($term)
    {
        return $this->getReportsByCondition([
            ['judul', 'like', "%$term%"],
            ['deskripsi', 'like', "%$term%"]
        ]);
    }

    /**
     * Update a report. Checks if the current authenticated user is the owner.
     */
    public function updateReport($id, array $data, $newImage = null)
    {
        $report = Report::find($id);
        if (!$report) {
            return ['error' => self::MSG_NOT_FOUND, 'error_code' => 404];
        }
        if ($report->masyarakat->id !== auth()->id()) {
            return ['error' => self::MSG_UNAUTHORIZED, 'error_code' => 401];
        }
        if ($newImage) {
            $this->deleteImage($report->foto);
            $data['foto'] = $this->uploadImage($newImage, 'reports');
        }
        $report->update(array_intersect_key($data, array_flip(['judul', 'deskripsi', 'lokasi', 'status', 'id_pemerintah', 'id_kategori'])));
        return $report;
    }

    /**
     * Delete a report. Validates ownership before deletion.
     */
    public function deleteReport($id)
    {
        $report = Report::find($id);
        if (!$report) {
            return ['error' => self::MSG_NOT_FOUND];
        }
        if ($report->masyarakat->id !== auth()->id()) {
            return ['error' => self::MSG_UNAUTHORIZED];
        }
        $this->deleteImage($report->foto);
        $report->delete();
        return ['message' => 'Report deleted successfully'];
    }

    /**
     * Get discussion messages for a report.
     */
    public function getDiskusiForReport($reportId)
    {
        return Diskusi::where('id_report', $reportId)->with('user')->get();
    }

    /**
     * Retrieve reports liked by the current user.
     */
    public function getLikedReports()
    {
        $likedReportIds = RatingReport::where('id_user', auth('sanctum')->id())->pluck('id_report');
        return Report::whereIn('id', $likedReportIds)->paginate(10);
    }
}
