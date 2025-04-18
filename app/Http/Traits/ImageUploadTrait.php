<?php

namespace App\Http\Traits;

use Intervention\Image\Laravel\Facades\Image;

trait ImageUploadTrait
{
    /**
     * Upload an image to the specified folder and return the stored path.
     */
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

    /**
     * Delete an image from storage.
     */
    public function deleteImage($path)
    {
        $realPath = public_path(str_replace("storage/", "storage/", $path));

        $thumbnailPath = dirname($realPath) . '/thumbnail/' . basename($path);

        if (file_exists($realPath)) {
            unlink($realPath);
        }

        if (file_exists($thumbnailPath)) {
            unlink($thumbnailPath);
        }
    }
}
