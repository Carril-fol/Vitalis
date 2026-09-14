<?php
namespace App\Permissions\Services;

use App\Permissions\Models\Permission;
use App\Permissions\Repositories\PermissionRepository;
use App\Permissions\Interfaces\IPermissionService;
use App\Core\ValidationException;
use Exception;


class PermissionsService implements IPermissionService {
    /**
     * Summary of permissionRepository
     * @var PermissionRepository
     */
    private PermissionRepository $permissionRepository;
    
    /**
     * Summary of __construct
     * @param PermissionRepository $permissionRepository
     */
    public function __construct(PermissionRepository $permissionRepository)
    {
        $this->permissionRepository = $permissionRepository;
    }

    /**
     * Summary of permissionExists
     * @param string $name
     * @return bool
     */
    private function permissionExists(string $name): bool
    {
        return $this->permissionRepository->findByName($name) !== null;
    }
    
    /**
     * Summary of createPermission
     * @param string $name
     * @return void
     */
    public function createPermission(string $name): void
    {
        if ($this->permissionExists($name)) throw ValidationException::forField("name", "Ya existe un permiso con ese nombre.");
        
        $permission = new Permission();
        $permission->setName($name);
        $this->permissionRepository->save($permission);
    }

    /**
     * Summary of getPermissionByName
     * @param string $name
     * @return Permission|null
     */
    public function getPermissionByName(string $name): ?Permission
    {
        return $this->permissionRepository->findByName($name);
    }

    /**
     * Summary of getAllPermissions
     * @return array
     */
    public function getAllPermissions(): array
    {
        return $this->permissionRepository->getAll();
    }

    /**
     * Summary of getPermissionById
     * @param int $id
     * @return object|null
     */
    public function getPermissionById(int $id): ?Permission
    {
        return $this->permissionRepository->find($id);
    }

    /**
     * Summary of getPermissionsByIds
     * @param array $ids
     * @return Permission[]
     */
    public function getPermissionsByIds(array $ids): array
    {
        return $this->permissionRepository->findByIds($ids);
    }

    /**
     * Summary of deletePermission
     * @param string $name
     * @throws Exception
     * @return void
     */
    public function deletePermission(string $name): void
    {
        $permission = $this->permissionRepository->findByName($name);
        if (!$permission) throw new Exception("Permission not found");

        $this->permissionRepository->remove($permission);
    }

    /**
     * Summary of updatePermission
     * @param string $oldName
     * @param string $newName
     * @return void
     */
    public function updatePermission(string $oldName, string $newName): void
    {
        $permission = $this->permissionRepository->findByName($oldName);
        if ($permission) {
            $permission->setName($newName);
            $this->permissionRepository->save($permission);
        }
    }

    /**
     * Summary of findAll
     * @return array
     */
    public function findAll(): array
    {
        return $this->permissionRepository->getAll();
    }
}