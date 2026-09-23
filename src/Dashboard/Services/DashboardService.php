<?php
namespace App\Dashboard\Services;

use DateTimeImmutable;

use Doctrine\ORM\EntityManagerInterface;

use App\Specialities\Models\Speciality;
use App\Patients\Models\Patient;
use App\MedicalStaff\Models\MedicalStaff;
use App\Turns\Models\Turn;
use App\Turns\Models\TurnStatus;

class DashboardService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function countPatient()
    {
        return $this->em->getRepository(Patient::class)
            ->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->join('p.user', 'u')
            ->where('u.status = :status')
            ->setParameter('status', 'active')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countMedicalStaff()
    {
        return $this->em->getRepository(MedicalStaff::class)
            ->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->join('m.user', 'u')
            ->where('u.status = :status')
            ->setParameter('status', 'active')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countTurnToday(): int
    {
        $today = new DateTimeImmutable('today');

        $query = $this->em->getRepository(Turn::class)
            ->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->where('t.status != :cancelled')
            ->andWhere('t.startsAt >= :from')
            ->andWhere('t.startsAt < :to')
            ->setParameter('cancelled', TurnStatus::Cancelled)
            ->setParameter('from', $today)
            ->setParameter('to', $today->modify('+1 day'));

        return (int) $query->getQuery()->getSingleScalarResult();
    }

    /** @return Turn[] */
    public function turnsToday(?MedicalStaff $medic = null): array
    {
        $today = new DateTimeImmutable('today');

        $query = $this->em->createQueryBuilder()
            ->select('t', 'p', 'pu', 'm', 'mu', 'r')
            ->from(Turn::class, 't')
            ->join('t.patient', 'p')->join('p.user', 'pu')
            ->join('t.medicalStaff', 'm')->join('m.user', 'mu')
            ->join('t.consultingRoom', 'r')
            ->where('t.startsAt >= :from')
            ->andWhere('t.startsAt < :to')
            ->setParameter('from', $today)
            ->setParameter('to', $today->modify('+1 day'))
            ->orderBy('t.startsAt', 'ASC');

        if ($medic !== null) {
            $query->andWhere('t.medicalStaff = :medic')->setParameter('medic', $medic);
        }

        return $query->getQuery()->getResult();
    }

    public function countSpeciality()
    {
        return $this->em->getRepository(Speciality::class)
            ->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
