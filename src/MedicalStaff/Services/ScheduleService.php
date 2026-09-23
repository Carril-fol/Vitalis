<?php
namespace App\MedicalStaff\Services;

use DateTimeImmutable;

use Doctrine\ORM\EntityManagerInterface;

use App\MedicalStaff\Models\MedicalAbsence;
use App\MedicalStaff\Models\MedicalSchedule;
use App\MedicalStaff\Models\MedicalStaff;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use App\Specialities\Models\Speciality;
use App\Users\Models\UserStatus;

class ScheduleService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    /** @return MedicalSchedule[] */
    public function schedulesOf(MedicalStaff $medic): array
    {
        return $this->em->getRepository(MedicalSchedule::class)
            ->findBy(['medic' => $medic], ['weekday' => 'ASC', 'startTime' => 'ASC']);
    }

    /** @return MedicalAbsence[] */
    public function absencesOf(MedicalStaff $medic): array
    {
        return $this->em->getRepository(MedicalAbsence::class)
            ->findBy(['medic' => $medic], ['startsAt' => 'DESC']);
    }

    /** @return MedicalAbsence[] */
    public function holidays(): array
    {
        return $this->em->getRepository(MedicalAbsence::class)
            ->findBy(['medic' => null], ['startsAt' => 'DESC']);
    }

    /** @return MedicalAbsence[] */
    public function absencesBetween(Speciality $speciality, DateTimeImmutable $from, DateTimeImmutable $to): array
    {
        return $this->em->getRepository(MedicalAbsence::class)
            ->createQueryBuilder('absence')
            ->leftJoin('absence.medic', 'medic')
            ->andWhere('absence.medic IS NULL OR medic.speciality = :speciality')
            ->andWhere('absence.startsAt < :to')
            ->andWhere('absence.endsAt > :from')
            ->setParameter('speciality', $speciality)
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->getQuery()
            ->getResult();
    }

    public function conflictMessage(MedicalSchedule $schedule): ?string
    {
        $other = $this->em->createQueryBuilder()
            ->select('s', 'm', 'u', 'r')
            ->from(MedicalSchedule::class, 's')
            ->join('s.medic', 'm')
            ->join('m.user', 'u')
            ->join('s.consultingRoom', 'r')
            ->where('s.weekday = :weekday')
            ->andWhere('s.startTime < :end')
            ->andWhere('s.endTime > :start')
            ->andWhere('s.consultingRoom = :room OR s.medic = :medic')
            ->setParameter('weekday', $schedule->getWeekday())
            ->setParameter('start', $schedule->getStartTime(), 'time_immutable')
            ->setParameter('end', $schedule->getEndTime(), 'time_immutable')
            ->setParameter('room', $schedule->getConsultingRoom())
            ->setParameter('medic', $schedule->getMedic())
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if ($other === null) {
            return null;
        }

        $range = sprintf('%s de %s a %s', $other->getWeekdayName(), $other->getStartTime()->format('H:i'), $other->getEndTime()->format('H:i'));

        if ($other->getMedic() === $schedule->getMedic()) {
            return sprintf('Ya atiende el %s en %s.', $range, $other->getConsultingRoom()->getName());
        }

        return sprintf('%s ya está ocupado el %s por %s.', $other->getConsultingRoom()->getName(), $range, $other->getMedic()->getUser()->fullName());
    }

    public function save(MedicalSchedule|MedicalAbsence $item): void
    {
        $this->em->persist($item);
        $this->em->flush();
    }

    public function removeOwnAbsence(MedicalAbsence $absence, MedicalStaff $medic): void
    {
        if ($absence->getMedic()?->getId() !== $medic->getId()) {
            throw new AccessDeniedException('Esa ausencia no es tuya.');
        }

        $this->remove($absence);
    }

    public function remove(MedicalSchedule|MedicalAbsence $item): void
    {
        $this->em->remove($item);
        $this->em->flush();
    }

    /** @return MedicalSchedule[] */
    public function schedulesOfSpeciality(Speciality $speciality): array
    {
        return $this->em->getRepository(MedicalSchedule::class)
            ->createQueryBuilder('schedule')
            ->join('schedule.medic', 'medic')
            ->join('medic.user', 'user')
            ->join('schedule.consultingRoom', 'room')
            ->addSelect('medic', 'user', 'room')
            ->andWhere('medic.speciality = :speciality')
            ->andWhere('user.status = :activeUser')
            ->andWhere('room.active = true')
            ->setParameter('speciality', $speciality)
            ->setParameter('activeUser', UserStatus::Active)
            ->orderBy('schedule.startTime', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
