<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

use App\Models\Admin;
use App\Models\Masyarakat;
use App\Models\Pemerintah;

abstract class Controller
{
    public function checkRole(string $role): bool
    {
        $models = [
            'masyarakat' => Masyarakat::class,
            'pemerintah' => Pemerintah::class,
            'admin' => Admin::class,
        ];

        if (!isset($models[$role])) {
            return false;
        }

        return $models[$role]::where('id', auth()->id())->exists();
    }


    public function checkOwner($id)
    {
        return $id == auth()->user()->id;
    }

    public function uploadImage($file, $path)
    {
        $imageName = time() . '.' . $file->extension();

        $destinationPath = public_path("storage/$path");
        $destinationPathThumbnail = $destinationPath . '/thumbnail';

        if (!file_exists($destinationPathThumbnail)) {
            mkdir($destinationPathThumbnail, 0755, true);
        }

        $img = Image::read($file->path());
        $img->coverDown(100, 100, "center")->save($destinationPathThumbnail . '/' . $imageName);

        $file->move($destinationPath, $imageName);

        return str_replace("public/", "", "storage/$path/$imageName");
    }


    public function deleteImage($filePath)
    {
        // Ubah "storage/" menjadi "public/storage/" agar sesuai dengan lokasi asli
        $realPath = public_path(str_replace("storage/", "storage/", $filePath));

        // Path untuk thumbnail (dalam folder "thumbnail")
        $thumbnailPath = dirname($realPath) . '/thumbnail/' . basename($filePath);

        // Hapus file utama jika ada
        if (file_exists($realPath)) {
            unlink($realPath);
        }

        // Hapus thumbnail jika ada
        if (file_exists($thumbnailPath)) {
            unlink($thumbnailPath);
        }
    }
}
