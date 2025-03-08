<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;

use App\Models\Admin;
use App\Models\Masyarakat;
use App\Models\Pemerintah;

abstract class Controller
{
    private const STORAGE_PATH = 'storage/';
    private const PUBLIC_PATH = 'public/';
    public function checkRole(string $role)
    {
        $models = [
            'masyarakat' => Masyarakat::class,
            'pemerintah' => Pemerintah::class,
            'admin' => Admin::class,
        ];
    
        if (!isset($models[$role])) {
            return false; // Role is not valid
        }
    
        $user = $models[$role]::where('id', auth()->user()->id)->first();
    
        return $user instanceof $models[$role];
    }

    public function checkOwner($id)
    {
        if ($id == auth()->user()->id) {
            return false;
        }
        return true;
    }

    public function uploadImage($file, $path){
        $foto = $file->store($path);
        return str_replace(self::PUBLIC_PATH, self::STORAGE_PATH, $foto);
    }

    public function deleteImage($path){
        if (Storage::exists(str_replace(self::STORAGE_PATH, self::PUBLIC_PATH, $path))){
            Storage::delete(str_replace(self::STORAGE_PATH, self::PUBLIC_PATH, $path));
        }
    }
}
