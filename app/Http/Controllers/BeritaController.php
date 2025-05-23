<?php

namespace App\Http\Controllers;

use App\Http\Requests\Berita\StoreBeritaRequest;
use App\Http\Requests\Berita\UpdateBeritaRequest;
use App\Http\Requests\Berita\SearchBeritaRequest;
use App\Http\Services\BeritaService;
use App\Http\Resources\Berita\BeritaResource;
use App\Http\Traits\ApiResponseTrait;
use Illuminate\Http\Response;
use Illuminate\Http\Request;

class BeritaController extends Controller
{
    use ApiResponseTrait;

    public function __construct(protected BeritaService $beritaService)
    {
    }

    /**
     * Display a paginated list of Berita.
     */
    public function index()
    {
        $paginated = $this->beritaService->getPaginatedBerita();
        return $this->success($paginated);
    }

    /**
     * Display a detail Berita.
     */
    public function show($id)
    {
        $data = $this->beritaService->getBeritaDetails($id);
        return $this->success($data);
    }

    /**
     * Display Berita filtered by category.
     */
    public function getByCategory($categoryId)
    {
        $result = $this->beritaService->getBeritaByCategory($categoryId);
        if ($result->total() === 0) {
            return $this->error('No berita found for this category', Response::HTTP_NOT_FOUND);
        }
        return $this->success($result);
    }

    /**
     * Create a new Berita entry.
     */
    public function store(StoreBeritaRequest $request)
    {
        $berita = $this->beritaService->createBerita(
            $request->validated(),
            $request->file('foto')
        );
        return $this->success(new BeritaResource($berita), Response::HTTP_CREATED);
    }

    /**
     * Update an existing Berita entry.
     */
    public function update(UpdateBeritaRequest $request, $id)
    {
        $result = $this->beritaService->updateBerita(
            $id,
            $request->validated(),
            $request->file('foto')
        );
        $status = isset($result['error']) ? $result['error_code'] : Response::HTTP_OK;
        return $status === Response::HTTP_OK
            ? $this->success($result)
            : $this->error($result['error'], $status);
    }

    /**
     * Delete a Berita entry.
     */
    public function destroy($id)
    {
        $result = $this->beritaService->deleteBerita($id);
        $status = isset($result['error']) ? Response::HTTP_NOT_FOUND : Response::HTTP_OK;
        return $status === Response::HTTP_OK
            ? $this->success($result)
            : $this->error($result['error'], $status);
    }

    /**
     * Search for Berita by title, content, or status.
     */
    public function searchBerita(SearchBeritaRequest $request)
    {
        $result = $this->beritaService->searchBerita(
            $request->validated()['search']
        );
        if (empty($result['data'])) {
            return $this->error('No reports found', Response::HTTP_NOT_FOUND);
        }
        return $this->success($result);
    }

    /**
     * Toggle like for a Berita entry.
     */
    public function like(Request $request)
    {
        $result = $this->beritaService->toggleLikeBerita($request->id);
        return $this->success(['success' => $result]);
    }
}
