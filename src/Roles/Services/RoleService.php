<?php
namespace App\Roles\Services;

use Doctrine\ORM\EntityManagerInterface;

use App\Roles\Models\Role;
use App\Users\Models\User;

class RoleService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function findAll(): array
    {
        return $this->em->getRepository(Role::class)->findBy([], ['name' => 'ASC']);
    }

    public function save(Role $role): void
    {
        $this->em->persist($role);
        $this->em->flush();
    }

    public function delete(Role $role): bool
    {
        if ($this->em->getRepository(User::class)->count(['role' => $role]) > 0) {
            return false;
        }

        $this->em->remove($role);
        $this->em->flush();
        return true;
    }
}
