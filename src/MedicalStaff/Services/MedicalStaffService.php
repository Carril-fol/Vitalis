<?php
namespace App\MedicalStaff\Services;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

use App\MedicalStaff\Models\MedicalStaff;
use App\Users\Models\User;

class MedicalStaffService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function ofUser(User $user): ?MedicalStaff
    {
        return $this->em->getRepository(MedicalStaff::class)->findOneBy(['user' => $user]);
    }

    /** @return MedicalStaff[] */
    public function findAll(): array
    {
        return $this->em->getRepository(MedicalStaff::class)->findAll();
    }

    public function save(MedicalStaff $medicalStaff, ?string $plainPassword): void
    {
        if ($plainPassword) {
            $user = $medicalStaff->getUser();
            $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));
        }

        $this->em->persist($medicalStaff);
        $this->em->flush();
    }

    public function changeStatus(MedicalStaff $medicalStaff, bool $active): void
    {
        $active ? $medicalStaff->getUser()->activate() : $medicalStaff->getUser()->deactivate();
        $this->em->flush();
    }
}
