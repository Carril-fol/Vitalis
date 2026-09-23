<?php
namespace App\Patients\Services;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

use App\Patients\Models\Patient;
use App\Roles\Models\Role;
use App\Roles\Models\RoleArea;

class PatientService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    /** @return Patient[] */
    public function findAll(): array
    {
        return $this->em->createQueryBuilder()
            ->select('p', 'u', 'r')
            ->from(Patient::class, 'p')
            ->join('p.user', 'u')
            ->join('u.role', 'r')
            ->orderBy('u.lastName')
            ->getQuery()
            ->getResult();
    }

    public function newPatient(): Patient
    {
        $role = $this->em->getRepository(Role::class)->findOneBy(['area' => RoleArea::Patient], ['id' => 'ASC']);

        if ($role === null) {
            throw new \LogicException('No hay ningún rol del área Pacientes. Creá uno desde Roles.');
        }

        $patient = new Patient();
        $patient->getUser()->setRole($role);

        return $patient;
    }

    public function save(Patient $patient, ?string $plainPassword): void
    {
        if ($plainPassword) {
            $user = $patient->getUser();
            $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));
        }

        $this->em->persist($patient);
        $this->em->flush();
    }

    public function changeStatus(Patient $patient, bool $active): void
    {
        $active ? $patient->getUser()->activate() : $patient->getUser()->deactivate();
        $this->em->flush();
    }
}