<?php
namespace App\HealthInsurances\Services;

use Doctrine\ORM\EntityManagerInterface;
use App\HealthInsurances\Models\HealthInsurance;

class HealthInsuranceService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    /** @return HealthInsurance[] */
    public function findAll(): array
    {
        return $this->em->createQueryBuilder()
            ->select('hi')
            ->from(HealthInsurance::class, 'hi')
            ->orderBy('hi.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function save(HealthInsurance $healthInsurance): void
    {
        $this->em->persist($healthInsurance);
        $this->em->flush();
    }
}
