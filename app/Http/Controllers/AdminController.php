<?php

namespace App\Http\Controllers;

use App\Http\Services\AdminService;
use App\Http\Requests\Admin\StorePemerintahRequest;
use App\Http\Requests\Admin\UpdatePemerintahRequest;
use App\Http\Requests\Admin\AuthAdminRequest;
use App\Http\Requests\Admin\SearchRequest;
use App\Http\Requests\Admin\UbahPasswordRequest;
use App\Http\Resources\Pemerintah\PemerintahResource;
use App\Http\Resources\Masyarakat\MasyarakatResource;
use App\Http\Traits\ApiResponseTrait;
use Illuminate\Http\Response;

class AdminController extends Controller
{
    use ApiResponseTrait;

    public function __construct(protected AdminService $adminService) {}
    

    /**
     * Register a new Pemerintah account.
     */
    public function storePemerintah(StorePemerintahRequest $request)
    {
        $this->adminService->storePemerintah(
            $request->validated(),
            $request->file('foto')
        );
        return response()->noContent();
    }

    /**
     * Update an existing Pemerintah account.
     */
    public function updatePemerintah(UpdatePemerintahRequest $request, $id)
    {
        $result = $this->adminService->updatePemerintah(
            $id,
            $request->validated(),
            $request->file('foto')
        );
        $status = $result['error'] ?? false ? $result['error_code'] : Response::HTTP_OK;
        return $status === Response::HTTP_OK
            ? $this->success($result)
            : $this->error($result['error'], $status);
    }


    /**
     * Display a paginated list of Pemerintah.
     */
    public function indexPemerintah()
    {
        app()->make(AuthAdminRequest::class);
        $data = $this->adminService->getPemerintahPaginated();
        return $this->success($data);
    }

    /**
     * Show details for a given Pemerintah.
     */
    public function showPemerintah($id)
    {
        app()->make(AuthAdminRequest::class);
        $data = $this->adminService->getPemerintahDetails($id);
        if (isset($data['error'])) {
            return $this->error($data['error'], Response::HTTP_NOT_FOUND);
        }
        return $this->success(new PemerintahResource($data));
    }

    /**
     * Search Pemerintah users.
     */
    public function searchPemerintah(SearchRequest $request)
    {
        $data = $this->adminService->searchPemerintah($request->validated()['search']);
        return $this->success($data);
    }

    /**
     * Delete a Pemerintah account and its related user.
     */
    public function destroyPemerintah(AuthAdminRequest $id)
    {
        $result = $this->adminService->deletePemerintah($id);
        $status = isset($result['error']) ? Response::HTTP_NOT_FOUND : Response::HTTP_OK;
        return $status === Response::HTTP_OK
            ? $this->success($result)
            : $this->error($result['error'], $status);
    }

    /**
     * Search a Masyarakat by phone.
     */
    public function searchMasyarakatByPhone(SearchRequest $request)
    {
        $result = $this->adminService->findMasyarakatByPhone($request->validated()['search']);
        if (isset($result['error'])) {
            return $this->error($result['error'], Response::HTTP_NOT_FOUND);
        }
        return $this->success(MasyarakatResource::collection($result));
    }

    /**
     * Update password for a user.
     */
    public function ubahPassword(UbahPasswordRequest $request)
    {
        $data = $request->validated();
        $result = $this->adminService->ubahPassword($data['username'], $data['new_password']);
        $status = isset($result['error']) ? Response::HTTP_NOT_FOUND : Response::HTTP_OK;
        return $status === Response::HTTP_OK
            ? $this->success($result)
            : $this->error($result['error'], $status);
    }
}
