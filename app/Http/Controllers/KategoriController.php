<?php

namespace App\Http\Controllers;

use App\Http\Requests\Kategori\StoreBeritaCategoryRequest;
use App\Http\Requests\Kategori\StoreReportCategoryRequest;

use App\Http\Services\KategoriService;
use App\Http\Traits\ApiResponseTrait;

class KategoriController extends Controller
{
    use ApiResponseTrait;
    public function __construct(protected KategoriService $kategoriService)
    {
    }

    /**
     * Display report categories.
     */
    public function indexReport()
    {
        $data = $this->kategoriService->getAllReportCategories();
        return $this->success($data);
    }

    /**
     * Display berita categories.
     */
    public function indexBerita()
    {
        $data = $this->kategoriService->getAllBeritaCategories();
        return $this->success($data);
    }

    /**
     * Store a new report category.
     */
    public function storeReport(StoreReportCategoryRequest $request)
    {
        $category = $this->kategoriService->createReportCategory(
            $request->validated()
        );
        return $this->success($category, 201);
    }

    /**
     * Store a new berita category.
     */
    public function storeBerita(StoreBeritaCategoryRequest $request)
    {
        $category = $this->kategoriService->createBeritaCategory(
            $request->validated(),
            $request->file('foto')
        );
        return $this->success($category, 201);
    }
}
