<?php
namespace App\Permissions\Interfaces;

use App\Permissions\Models\Permission;

interface IPermissionService 
{
    public function createPermission(string $name): void;
    public function getPermissionByName(string $name): ?Permission;
    public function getAllPermissions(): array;
    public function deletePermission(string $name): void;
    public function updatePermission(string $oldName, string $newName): void;
    public function findAll(): array;
    public function getPermissionById(int $id): ?Permission;

    /**
     * @param int[] $ids
     * @return Permission[]
     */
    public function getPermissionsByIds(array $ids): array;
} 