<?php

namespace App\Http\Services;

use App\Models\KategoriReport;
use App\Models\KategoriBerita;
use App\Http\Traits\ImageUploadTrait;

class KategoriService
{
    use ImageUploadTrait;

    /**
     * Return all report categories.
     */
    public function getAllReportCategories()
    {
        return KategoriReport::all();
    }

    /**
     * Return all berita categories.
     */
    public function getAllBeritaCategories()
    {
        return KategoriBerita::all();
    }

    /**
     * Create a new report category.
     */
    public function createReportCategory(array $data)
    {
        return KategoriReport::create($data);
    }

    /**
     * Create a new berita category.
     */
    public function createBeritaCategory(array $data, $foto)
    {
        $fotoPath = $this->uploadImage($foto, 'kategori/berita');
        return KategoriBerita::create([
            'name' => $data['name'],
            'foto' => $fotoPath,
        ]);
    }
}
