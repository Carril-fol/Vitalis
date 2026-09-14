<?php
namespace App\Roles\Repositories;

use App\Core\Repository;
use App\Roles\Models\Role;
use App\Roles\Models\RoleArea;


class RoleRepository extends Repository
{
    protected function entityClass(): string
    {
        return Role::class;
    }

    /**
     * Summary of find
     * @param int $id
     * @return T|null
     */
    public function find(int $id): ?Role
    {
        return $this->repository->find($id);
    }

    /**
     * Summary of findByName
     * @param string $name
     * @return T|null
     */
    public function findByName(string $name): ?Role
    {
        return $this->repository->findOneBy(array('name' => trim($name)));
    }

    /** @return Role[] */
    public function findByArea(RoleArea $area): array
    {
        return $this->repository->findBy(array('area' => $area), array('name' => 'ASC'));
    }
}
