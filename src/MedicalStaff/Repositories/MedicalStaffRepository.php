<?php
namespace App\MedicalStaff\Repositories;

use App\Core\Repository;
use App\MedicalStaff\Models\MedicalStaff;


class MedicalStaffRepository extends Repository
{
    protected function entityClass(): string
    {
        return MedicalStaff::class;
    }

    public function findByLicenseNumber(string $licenseNumber): array
    {
        return $this->withUser('m')
            ->andWhere('m.license_number = :licenseNumber')
            ->setParameter('license_number', $licenseNumber)
            ->getQuery()
            ->getResult();
    }

    public function findBySpeciality(int $specialityId): array
    {
        return $this->withUser('m')
            ->andWhere('m.speciality_id = :specialityId')
            ->setParameter('speciality_id', $specialityId)
            ->getQuery()
            ->getResult();
    }
}