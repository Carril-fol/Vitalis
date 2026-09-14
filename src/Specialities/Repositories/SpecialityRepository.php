<?php
namespace App\Specialities\Repositories;

use App\Core\Repository;
use App\Specialities\Models\Speciality;

class SpecialityRepository extends Repository
{
    protected function entityClass(): string
    {
        return Speciality::class;
    }

    public function find(int $id): ?Speciality
    {
        return $this->repository->find($id);
    }

    public function findByName(string $name): ?Speciality
    {
        return $this->repository->findOneBy(array('name' => trim($name)));
    }
}
