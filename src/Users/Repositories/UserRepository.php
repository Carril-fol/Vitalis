<?php
namespace App\Users\Repositories;

use App\Core\Repository;
use App\Roles\Models\Role;
use App\Users\Models\User;
use App\Users\Models\UserStatus;

class UserRepository extends Repository
{
    protected function entityClass(): string
    {
        return User::class;
    }

    public function find(int $id): ?User
    {
        return $this->repository->find($id);
    }

    public function findByDni(string $dni): ?User
    {
        return $this->repository->findOneBy(array('dni' => trim($dni)));
    }

    public function findByEmail(string $email): ?User
    {
        return $this->repository->findOneBy(array('email' => mb_strtolower(trim($email))));
    }

    public function findByRole(Role $role): array
    {
        return $this->repository->findBy(array('role' => $role));
    }

    public function findActive(): array
    {
        return $this->repository->findBy(array('status' => UserStatus::Active));
    }

    public function getAll(): array
    {
        return $this->repository->findAll();
    }
}
