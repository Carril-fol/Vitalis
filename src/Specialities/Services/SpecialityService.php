<?php

namespace App\Specialities\Services;

use Doctrine\ORM\EntityManagerInterface;

use App\Specialities\Models\Speciality;
use App\MedicalStaff\Models\MedicalStaff;

class SpecialityService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    /** @return Speciality[] */
    public function findAll(): array
    {
        return $this->em->getRepository(Speciality::class)->findBy([], ['name' => 'ASC']);
    }

    public function save(Speciality $speciality): void
    {
        $this->em->persist($speciality);
        $this->em->flush();
    }

    public function delete(Speciality $speciality): bool
    {
        if ($this->em->getRepository(MedicalStaff::class)->count(['speciality' => $speciality]) > 0) {
            return false;
        }

        $this->em->remove($speciality);
        $this->em->flush();

        return true;
    }
}
