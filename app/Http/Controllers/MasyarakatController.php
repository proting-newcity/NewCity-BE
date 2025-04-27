<?php

namespace App\Http\Controllers;

use App\Http\Requests\Masyarakat\NotificationRequest;
use App\Http\Services\MasyarakatService;
use App\Http\Requests\Masyarakat\UpdateMasyarakatRequest;
use App\Http\Traits\ApiResponseTrait;
use Illuminate\Http\Response;


class MasyarakatController extends Controller
{
    use ApiResponseTrait;

    public function __construct(protected MasyarakatService $masyarakatService)
    {
    }

    /**
     * Update an existing Masyarakat account.
     */
    public function updateMasyarakat(UpdateMasyarakatRequest $request)
    {
        $result = $this->masyarakatService->updateMasyarakat(
            $request->validated(),
            $request->file('foto')
        );
        $status = $result['error'] ?? false ? $result['error_code'] : Response::HTTP_OK;
        return $status === Response::HTTP_OK
            ? $this->success($result)
            : $this->error($result['error'], $status);
    }

    public function notification(NotificationRequest $request)
    {
        // Masyarakat instance via relationship
        $masyarakat = $request->user()->masyarakat;

        $page    = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);

        $result = $this->masyarakatService->getNotifications($masyarakat, $perPage, $page);

        return $this->success(data: $result);
    }
}
