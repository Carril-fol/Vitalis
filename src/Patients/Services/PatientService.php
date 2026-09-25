<?php
namespace App\Patients\Services;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

use App\Patients\Models\Patient;
use App\Roles\Models\Role;
use App\Roles\Models\RoleArea;
use App\Users\Services\InitialPasswordMailer;

class PatientService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly InitialPasswordMailer $initialPasswordMailer,
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
        $user = $patient->getUser();
        $isNew = $user->id() === null;
        $plainPassword ??= $isNew ? $user->initialPassword() : null;

        if ($plainPassword) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));
        }

        $this->em->persist($patient);
        $this->em->flush();

        if ($isNew) {
            $this->initialPasswordMailer->send($user);
        }
    }

    public function changeStatus(Patient $patient, bool $active): void
    {
        $active ? $patient->getUser()->activate() : $patient->getUser()->deactivate();
        $this->em->flush();
    }
}