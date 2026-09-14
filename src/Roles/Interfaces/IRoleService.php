<?php
namespace App\Roles\Interfaces;

use App\Core\NotFoundException;
use App\Core\ValidationException;
use App\Roles\Models\Role;
use App\Roles\Models\RoleArea;
use App\Roles\Schemas\CreateRole;
use App\Roles\Schemas\UpdateRole;

interface IRoleService {
    public function getAllRoles(): array;

    /** @throws NotFoundException */
    public function getRoleById(int $id): Role;

    /** @return Role[] */
    public function getRolesByArea(RoleArea $area): array;

    /** @return int[] */
    public function getPermissionIds(Role $role): array;

    /** @throws ValidationException */
    public function createRole(CreateRole $schema): int;

    /**
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function updateRole(int $id, UpdateRole $schema): void;

    public function deleteRole(int $id): bool;
}
