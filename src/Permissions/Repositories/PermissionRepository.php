<?php
namespace App\Permissions\Repositories;

use App\Core\Repository;
use App\Permissions\Models\Permission;

class PermissionRepository extends Repository
{
    protected function entityClass(): string
    {
        return Permission::class;
    }

    public function findByName(string $name): ?Permission
    {
        return $this->repository->findOneBy(array('name' => trim($name)));
    }

    public function getAll(): array
    {
        return $this->repository->findAll();
    }

    /**
     * @param int[] $ids
     * @return Permission[] Solo los que existen: si falta alguno, el resultado es mas corto.
     */
    public function findByIds(array $ids): array
    {
        if ($ids === array()) {
            return array();
        }

        return $this->repository->findBy(array('id' => $ids));
    }
}