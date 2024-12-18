<?php

namespace App\Http\Controllers;
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
}
