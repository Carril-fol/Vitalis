<?php
namespace App\Roles\Services;

use Symfony\Component\Validator\Validator\ValidatorInterface;

use App\Core\NotFoundException;
use App\Core\ValidationException;

use App\RolePermissions\Interfaces\IRolePermissionsService;
use App\Users\Interfaces\IUserService;

use App\Roles\Models\Role;
use App\Roles\Models\RoleArea;
use App\Roles\Schemas\CreateRole;
use App\Roles\Schemas\UpdateRole;
use App\Roles\Interfaces\IRoleService;
use App\Roles\Repositories\RoleRepository;


class RoleService implements IRoleService
{
    private RoleRepository $roleRepository;
    private IRolePermissionsService $rolePermissionsService;
    private IUserService $userService;
    private ValidatorInterface $validator;

    public function __construct(
        RoleRepository $roleRepository,
        IRolePermissionsService $rolePermissionsService,
        IUserService $userService,
        ValidatorInterface $validator
    ) {
        $this->roleRepository = $roleRepository;
        $this->rolePermissionsService = $rolePermissionsService;
        $this->userService = $userService;
        $this->validator = $validator;
    }

    /**
     * Summary of assertValid
     * @param CreateRole $schema
     * @return void
     */
    private function assertValid(CreateRole $schema): void
    {
        $violations = $this->validator->validate($schema);

        if (count($violations) > 0) {
            throw ValidationException::fromViolations($violations);
        }
    }

    /**
     * @throws ValidationException
     */
    private function assertIsNameAvailable(string $name): void
    {
        $role = $this->roleRepository->findByName($name);
        if ($role != null)
            throw ValidationException::forField('name', 'Ya existe un rol con ese nombre.');

    }

    /**
     * Summary of getAllRoles
     * @return array
     */
    public function getAllRoles(): array
    {
        return $this->roleRepository->findAll();
    }

    /**
     * Summary of getRoleById
     * @param int $id
     * @throws NotFoundException
     * @return Role
     */
    public function getRoleById(int $id): Role
    {
        $role = $this->roleRepository->find($id);

        if (!$role)
            throw new NotFoundException("Role not found");
        return $role;
    }

    /**
     * Summary of getRolesByArea
     * @param RoleArea $area
     * @return Role[]
     */
    public function getRolesByArea(RoleArea $area): array
    {
        return $this->roleRepository->findByArea($area);
    }

    /**
     * Summary of getPermissionIds
     * @param Role $role
     * @return int[]
     */
    public function getPermissionIds(Role $role): array
    {
        return $this->rolePermissionsService->getPermissionIds($role);
    }

    /**
     * Summary of createRole
     * @param CreateRole $schema
     * @return int|null
     */
    public function createRole(CreateRole $schema): int
    {
        $this->assertValid($schema);
        $this->assertIsNameAvailable($schema->name);

        $role = new Role($schema->name, $schema->areaValue());
        $this->roleRepository->save($role, false);
        $this->rolePermissionsService->syncPermissions($role, $schema->permissionIds);
        $this->roleRepository->flush();

        return $role->getId();
    }

    /**
     * Summary of updateRole
     * @param int $id
     * @param UpdateRole $schema
     * @return void
     */
    public function updateRole(int $id, UpdateRole $schema): void
    {
        $role = $this->getRoleById($id);
        $this->assertValid($schema);

        if ($schema->name != $role->name()) $this->assertIsNameAvailable($schema->name);

        $role->setName($schema->name);
        $role->setArea($schema->areaValue());
        $this->rolePermissionsService->syncPermissions($role, $schema->permissionIds);
        $this->roleRepository->flush();
    }

    /**
     * Summary of deleteRole
     * @param int $id
     * @return bool
     */
    public function deleteRole(int $id): bool
    {
        $role = $this->roleRepository->find($id);
        if (!$role)
            return false;

        $users = $this->userService->getUserByRole($role);
        if (count($users) > 0) {
            throw ValidationException::forField('role', 'No se puede borrar el rol, ya que hay usuarios que lo tienen asignado.');
        }

        $this->roleRepository->remove($role);
        return true;
    }
}
