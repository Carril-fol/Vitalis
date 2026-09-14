<?php
namespace App\Administratives\Repositories;


use App\Core\Repository;
use App\Users\Models\User;
use App\Administratives\Models\Administrative;


class AdministrativeRepository extends Repository
{   
    /**
     * Summary of entityClass
     * @return string
     */
    protected function entityClass(): string
    {
        return Administrative::class;
    }

    /**
     * Summary of find
     * @param int $id
     */
    public function find(int $id): ?Administrative
    {
        return $this->repository->find($id);
    }

    /**
     * Summary of findByUser
     * @param User|int $user
     */
    public function findByUser(User|int $user): ?Administrative
    {
        return $this->repository->findOneBy(['user' => $user]);
    }

    /**
     * Summary of findBySector
     * @param string $sector
     * @return array
     */
    public function findBySector(string $sector): array
    {
        return $this->withUser()
            ->andWhere('a.sector = :sector')
            ->setParameter('sector', trim($sector))
            ->getQuery()
            ->getResult();
    }

    /**
     * Summary of findByName
     * @param string $name
     * @return array
     */
    public function findByName(string $name): array
    {
        return $this->withUser()
            ->andWhere("CONCAT(u.firstName, ' ', u.lastName) LIKE :name")
            ->setParameter('name', '%' . trim($name) . '%')
            ->getQuery()
            ->getResult();
    }

    /**
     * Summary of findAllWithUser
     * @return array
     */
    public function findAllWithUser(): array
    {
        return $this->withUser()
            ->getQuery()
            ->getResult();
    }
}