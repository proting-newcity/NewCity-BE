<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;

use App\Models\Admin;
use App\Models\Masyarakat;
use App\Models\Pemerintah;

abstract class Controller
{
    public function checkRole(string $role)
    {

        if ($role == "masyarakat") {
            $masyarakat = Masyarakat::where('id', auth()->user()->id)->first();

            if (!($masyarakat instanceof Masyarakat)) {
                return false;
            }
        } else if ($role == "pemerintah") {
            $pemerintah = Pemerintah::where('id', auth()->user()->id)->first();

            if (!($pemerintah instanceof Pemerintah)) {
                return false;
            }
        } else if ($role == "admin") {
            $admin = Admin::where('id', auth()->user()->id)->first();

            if (!($admin instanceof Admin)) {
                return false;
            }
        }
        return true;
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
        return str_replace('public/', 'storage/', $foto);
    }

    public function deleteImage($path){
        if (Storage::exists(str_replace('storage/', 'public/', $path))){
            Storage::delete(str_replace('storage/', 'public/', $path));
        }
    }
}
