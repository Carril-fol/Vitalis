<?php
namespace App\RolePermissions\Repository;

use App\Core\Repository;
use App\RolePermissions\Models\RolePermission;

class RolePermissionsRepository extends Repository
{
    protected function entityClass(): string
    {
        return RolePermission::class;
    }

    public function roleHasPermission(int $roleId, string $permissionName): bool
    {
        return (bool) $this->em->createQueryBuilder()
            ->select('COUNT(rp.id)')
            ->from(RolePermission::class, 'rp')
            ->join('rp.permission', 'p')
            ->where('rp.role = :roleId')
            ->andWhere('p.name = :name')
            ->setParameter('roleId', $roleId)
            ->setParameter('name', $permissionName)
            ->getQuery()
            ->getSingleScalarResult();
    }
}