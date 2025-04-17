<?php

namespace App\Http\Controllers;


use App\Http\Services\InstitusiService;
use App\Http\Requests\Institusi\StoreInstitusiRequest;
use App\Http\Requests\Institusi\UpdateInstitusiRequest;
use App\Http\Resources\Institusi\InstitusiResource;
use App\Http\Traits\ApiResponseTrait;
use Illuminate\Http\Response;

class InstitusiController extends Controller
{
    use ApiResponseTrait;

    private const INSTITUSI_NOT_FOUND = 'Institusi not found';

    public function __construct(protected InstitusiService $institusiService)
    {
    }

    /**
     * Display a listing of all institusi.
     */
    public function index()
    {
        $all = $this->institusiService->getAll();
        return $this->success(InstitusiResource::collection($all));
    }

    /**
     * Display the specified institusi.
     */
    public function show($id)
    {
        $item = $this->institusiService->findById($id);
        if (!$item) {
            return $this->error(self::INSTITUSI_NOT_FOUND, Response::HTTP_NOT_FOUND);
        }
        return $this->success(new InstitusiResource($item));
    }

    /**
     * Store a newly created institusi.
     */
    public function store(StoreInstitusiRequest $request)
    {
        $data = $request->validated();
        $created = $this->institusiService->create($data);
        return $this->success(new InstitusiResource($created), Response::HTTP_CREATED);
    }

    /**
     * Update the specified institusi.
     */
    public function update(UpdateInstitusiRequest $request, $id)
    {
        $existing = $this->institusiService->findById($id);
        if (!$existing) {
            return $this->error(self::INSTITUSI_NOT_FOUND, Response::HTTP_NOT_FOUND);
        }

        $updated = $this->institusiService->update($id, $request->validated());
        return $this->success(new InstitusiResource($updated));
    }

    /**
     * Remove the specified institusi.
     */
    public function destroy($id)
    {
        $existing = $this->institusiService->findById($id);
        if (!$existing) {
            return $this->error(self::INSTITUSI_NOT_FOUND, Response::HTTP_NOT_FOUND);
        }

        $this->institusiService->delete($id);
        return $this->success(['message' => 'Institusi deleted successfully']);
    }
}
