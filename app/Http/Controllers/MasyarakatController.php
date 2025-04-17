<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\Masyarakat\NotificationRequest;
use App\Services\MasyarakatService;
use App\Http\Traits\ApiResponseTrait;


class MasyarakatController extends Controller
{
    use ApiResponseTrait;

    public function __construct(protected MasyarakatService $notifications)
    {
    }

    public function notification(NotificationRequest $request)
    {
        // Masyarakat instance via relationship
        $masyarakat = $request->user()->masyarakat;

        $page    = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);

        $result = $this->notifications->getNotifications($masyarakat, $perPage, $page);

        return $this->success($result);
    }
}
