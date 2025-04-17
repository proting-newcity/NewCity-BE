<?php

namespace App\Services;

use App\Models\Masyarakat;
use App\Models\Pemerintah;
use App\Models\Admin;

class RoleService
{
    protected array $models = [
        'masyarakat'  => Masyarakat::class,
        'pemerintah'  => Pemerintah::class,
        'admin'       => Admin::class,
    ];

    /**
     * Check if given user ID belongs to a specific role.
     */
    public function hasRole(int $userId, string $role): bool
    {
        if (!isset($this->models[$role])) {
            return false;
        }

        $model = $this->models[$role];
        return $model::where('id', $userId)->exists();
    }
}
