<?php
namespace App\RolePermissions\Services;

use App\Core\ValidationException;
use App\RolePermissions\Models\RolePermission;

use App\RolePermissions\Repository\RolePermissionsRepository;
use App\RolePermissions\Interfaces\IRolePermissionsService;

use App\Roles\Models\Role;
use App\Permissions\Interfaces\IPermissionService;


/**
 * No depende de IRoleService: recibe el Role ya armado. RoleService si depende
 * de este service (para asignar permisos al crear o editar un rol), asi que
 * pedir IRoleService aca arma una dependencia circular.
 */
class RolePermissionsService implements IRolePermissionsService {
    private RolePermissionsRepository $rolePermissionsRepository;
    private IPermissionService $permissionService;

    public function __construct(
        RolePermissionsRepository $rolePermissionsRepository,
        IPermissionService $permissionService
    ) {
        $this->rolePermissionsRepository = $rolePermissionsRepository;
        $this->permissionService = $permissionService;
    }

    public function roleHasPermission(int $roleId, string $permissionName): bool
    {
        return $this->rolePermissionsRepository->roleHasPermission($roleId, $permissionName);
    }

    public function getPermissionIds(Role $role): array
    {
        return array_map(
            fn(RolePermission $rolePermission) => $rolePermission->getPermission()->getId(),
            $this->currentOf($role)
        );
    }

    public function syncPermissions(Role $role, array $permissionIds): void
    {
        $permissions = $this->permissionService->getPermissionsByIds($permissionIds);

        if (count($permissions) !== count($permissionIds)) {
            throw ValidationException::forField('permissionIds', 'Hay un permiso que no existe.');
        }

        // Se borran solo los que sobran y se agregan solo los que faltan. Borrar
        // todo y volver a crear no sirve: en un mismo flush Doctrine hace los
        // INSERT antes que los DELETE, y el par repetido choca con
        // role_permission_unique.
        $kept = array();

        foreach ($this->currentOf($role) as $rolePermission) {
            $id = $rolePermission->getPermission()->getId();

            if (in_array($id, $permissionIds, true)) {
                $kept[] = $id;
            } else {
                $this->rolePermissionsRepository->remove($rolePermission, false);
            }
        }

        foreach ($permissions as $permission) {
            if (!in_array($permission->getId(), $kept, true)) {
                $this->rolePermissionsRepository->save(new RolePermission($role, $permission), false);
            }
        }
    }

    /**
     * Un rol recien creado todavia no tiene id, y Doctrine no acepta una
     * entidad sin id como parametro de busqueda: no tiene permisos guardados.
     *
     * @return RolePermission[]
     */
    private function currentOf(Role $role): array
    {
        if ($role->id() === null) {
            return array();
        }

        return $this->rolePermissionsRepository->findBy(array('role' => $role));
    }
}
