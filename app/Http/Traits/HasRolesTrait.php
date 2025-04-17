<?php
namespace App\Http\Traits;

use App\Services\RoleService;

trait HasRolesTrait
{
    /**
     * Determine if the current user has the given role.
     */
    public function hasRole(string $role): bool
    {
        // resolve RoleService from container
        return app(RoleService::class)->hasRole($this->id, $role);
    }
}
