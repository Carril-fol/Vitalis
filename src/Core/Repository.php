<?php

namespace App\Core;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;


abstract class Repository
{
    protected EntityManagerInterface $em;

    protected EntityRepository $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->repository = $em->getRepository($this->entityClass());
    }

    abstract protected function entityClass(): string;


    public function find(int $id): ?object
    {
        return $this->repository->find($id);
    }

    public function findAll(): array
    {
        return $this->repository->findAll();
    }

    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        return $this->repository->findBy($criteria, $orderBy, $limit, $offset);
    }

    public function findOneBy(array $criteria, ?array $orderBy = null): ?object
    {
        return $this->repository->findOneBy($criteria, $orderBy);
    }

    public function count(array $criteria = []): int
    {
        return $this->repository->count($criteria);
    }

    public function save(object $entity, bool $flush = true): void
    {
        $this->em->persist($entity);

        if ($flush) {
            $this->em->flush();
        }
    }

    public function remove(object $entity, bool $flush = true): void
    {
        $this->em->remove($entity);

        if ($flush) {
            $this->em->flush();
        }
    }

    public function flush(): void
    {
        $this->em->flush();
    }

    protected function withUser(
        string $rootAlias = 'a',
        string $userField = 'user',
        string $roleField = 'role',
        string $orderField = 'lastName'
    ): QueryBuilder {
        return $this->em->createQueryBuilder()
            ->select($rootAlias, 'u', 'r')
            ->from($this->entityClass(), $rootAlias)
            ->join("$rootAlias.$userField", 'u')
            ->join("u.$roleField", 'r')
            ->orderBy("u.$orderField", 'ASC');
    }
}
