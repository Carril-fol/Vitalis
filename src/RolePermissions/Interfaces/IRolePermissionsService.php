<?php
namespace App\RolePermissions\Interfaces;

use App\Core\ValidationException;
use App\Roles\Models\Role;

interface IRolePermissionsService
{
    /** 
     * @param int $roleId
     * @param string $permissionName
     * @return bool
    */
    public function roleHasPermission(int $roleId, string $permissionName): bool;

    /** @return int[] */
    public function getPermissionIds(Role $role): array;

    /**
     * @param int[] $permissionIds
     * @throws ValidationException
     */
    public function syncPermissions(Role $role, array $permissionIds): void;
}
