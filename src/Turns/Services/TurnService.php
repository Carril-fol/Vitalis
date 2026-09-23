<?php
namespace App\Turns\Services;

use DateInterval;
use DateTimeImmutable;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\OffsetPaginator;
use Doctrine\ORM\Tools\Pagination\Window;
use Doctrine\ORM\Tools\Pagination\WindowPage;

use App\MedicalStaff\Models\MedicalAbsence;
use App\MedicalStaff\Models\MedicalSchedule;
use App\MedicalStaff\Models\MedicalStaff;
use App\Patients\Models\Patient;
use App\Specialities\Models\Speciality;
use App\Turns\Models\Turn;
use App\Turns\Models\TurnStatus;
use App\Users\Models\User;

use App\MedicalStaff\Services\ScheduleService;
use App\MedicalStaff\Services\MedicalStaffService;

class TurnService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        public ScheduleService $scheduleService,
        private readonly MedicalStaffService $medics,
    ) {
    }

    private function assertOwns(Turn $turn, User $by): void
    {
        $medic = $this->medics->ofUser($by);

        if ($medic !== null && $turn->getMedicalStaff()?->getId() !== $medic->getId()) {
            throw new AccessDeniedException('Ese turno es de otro profesional.');
        }
    }

    private function takenSlots(Speciality $speciality, DateTimeImmutable $from, DateTimeImmutable $to): array
    {
        $rows = $this->em->getRepository(Turn::class)
            ->createQueryBuilder('turn')
            ->select('IDENTITY(turn.medicalStaff) AS medicId', 'turn.startsAt')
            ->join('turn.medicalStaff', 'medic')
            ->andWhere('medic.speciality = :speciality')
            ->andWhere('turn.startsAt >= :from')
            ->andWhere('turn.startsAt < :to')
            ->andWhere('turn.status != :cancelled')
            ->setParameter('speciality', $speciality)
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->setParameter('cancelled', TurnStatus::Cancelled)
            ->getQuery()
            ->getArrayResult();

        $taken = [];

        foreach ($rows as $row) {
            $taken[(int) $row['medicId']][$row['startsAt']->getTimestamp()] = true;
        }

        return $taken;
    }

    private function slotsOf(MedicalSchedule $schedule, DateTimeImmutable $day): array
    {
        if ($schedule->getWeekday() !== (int) $day->format('N')) {
            return [];
        }

        $start = $day->setTime(
            (int) $schedule->getStartTime()->format('H'),
            (int) $schedule->getStartTime()->format('i')
        );

        $end = $day->setTime(
            (int) $schedule->getEndTime()->format('H'),
            (int) $schedule->getEndTime()->format('i')
        );

        $interval = new DateInterval(
            'PT' . $schedule->getSlotMinutes() . 'M'
        );

        $slots = [];

        for ($slotStart = $start; $slotStart < $end; $slotStart = $slotStart->add($interval)) {
            $slotEnd = $slotStart->add($interval);

            if ($slotEnd > $end) {
                break;
            }

            $slots[] = new TurnSlot(
                $schedule->getMedic(),
                $schedule->getConsultingRoom(),
                $slotStart,
                $slotEnd
            );
        }

        return $slots;
    }

    /** @return array<int, MedicalSchedule[]> */
    private function schedulesByWeekday(Speciality $speciality): array
    {
        $byWeekday = [];

        foreach ($this->scheduleService->schedulesOfSpeciality($speciality) as $schedule) {
            $byWeekday[(int) $schedule->getWeekday()][] = $schedule;
        }

        return $byWeekday;
    }

    private function turnsQuery(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('t', 'p', 'pu', 'm', 'mu', 'r')
            ->from(Turn::class, 't')
            ->join('t.patient', 'p')->join('p.user', 'pu')
            ->join('t.medicalStaff', 'm')->join('m.user', 'mu')
            ->join('t.consultingRoom', 'r')
            ->orderBy('t.startsAt', 'DESC');
    }

    private function paginate(QueryBuilder $query, int $page, int $perPage): WindowPage
    {
        return (new OffsetPaginator())->paginate($query, Window::fromPageNumberAndSize(max(1, $page), $perPage));
    }

    /** @return WindowPage<Turn> */
    public function findAll(int $page = 1, int $perPage = 20): WindowPage
    {
        return $this->paginate($this->turnsQuery(), $page, $perPage);
    }

    public function countBetween(MedicalStaff $medic, DateTimeImmutable $from, DateTimeImmutable $to): int
    {
        return (int) $this->em->getRepository(Turn::class)
            ->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->where('t.medicalStaff = :medic')
            ->andWhere('t.status != :cancelled')
            ->andWhere('t.startsAt < :to')
            ->andWhere('t.endsAt > :from')
            ->setParameter('medic', $medic)
            ->setParameter('cancelled', TurnStatus::Cancelled)
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /** @return WindowPage<Turn> */
    public function findAllOfMedic(MedicalStaff $medic, int $page = 1, int $perPage = 20): WindowPage
    {
        $query = $this->turnsQuery()
            ->andWhere('t.medicalStaff = :medic')
            ->setParameter('medic', $medic);

        return $this->paginate($query, $page, $perPage);
    }

    /** @return TurnSlot[] */
    public function availableSlots(Speciality $speciality, int $days = 30): array
    {
        $byWeekday = $this->schedulesByWeekday($speciality);

        if ($byWeekday === []) {
            return [];
        }

        $slots = [];
        $today = new DateTimeImmutable('today');
        $until = $today->modify("+$days days");
        $now = new DateTimeImmutable();
        $taken = $this->takenSlots($speciality, $today, $until);
        $absences = $this->scheduleService->absencesBetween($speciality, $today, $until);

        for ($i = 0; $i < $days; $i++) {
            $day = $today->modify("+$i days");
            $weekday = (int) $day->format('N');

            if (!isset($byWeekday[$weekday])) {
                continue;
            }

            foreach ($byWeekday[$weekday] as $schedule) {
                foreach ($this->slotsOf($schedule, $day) as $slot) {
                    if ($slot->startsAt <= $now) {
                        continue;
                    }

                    $medicId = $slot->medic->getId();
                    $startsAt = $slot->startsAt->getTimestamp();

                    if (isset($taken[$medicId][$startsAt])) {
                        continue;
                    }

                    if ($this->onAbsence($absences, $slot)) {
                        continue;
                    }

                    $slots[] = $slot;
                }
            }
        }

        usort($slots, static fn(TurnSlot $a, TurnSlot $b) => $a->startsAt <=> $b->startsAt);
        return $slots;
    }

    /** @param MedicalAbsence[] $absences */
    private function onAbsence(array $absences, TurnSlot $slot): bool
    {
        foreach ($absences as $absence) {
            $medic = $absence->getMedic();

            if ($medic !== null && $medic->getId() !== $slot->medic->getId()) {
                continue;
            }

            if ($absence->getStartsAt() < $slot->endsAt && $absence->getEndsAt() > $slot->startsAt) {
                return true;
            }
        }

        return false;
    }

    public function findSlot(MedicalStaff $medic, DateTimeImmutable $startsAt): ?TurnSlot
    {
        $speciality = $medic->getSpeciality();

        if ($speciality === null) {
            return null;
        }

        foreach ($this->availableSlots($speciality) as $slot) {
            if ($slot->medic->getId() === $medic->getId() && $slot->startsAt == $startsAt) {
                return $slot;
            }
        }

        return null;
    }

    public function bookSlot(
        Patient $patient,
        MedicalStaff $medic,
        DateTimeImmutable $startsAt,
        User $createdBy,
        ?string $reason = null,
    ): Turn {
        $slot = $this->findSlot($medic, $startsAt)
            ?? throw new TurnUnavailable('Ese horario ya no está disponible. Elegí otro.');

        $turn = new Turn();
        $turn->setPatient($patient);
        $turn->setMedicalStaff($slot->medic);
        $turn->setConsultingRoom($slot->room);
        $turn->setStartsAt($slot->startsAt);
        $turn->setEndsAt($slot->endsAt);
        $turn->setCreatedBy($createdBy);
        $turn->setReason($reason);

        try {
            $this->em->persist($turn);
            $this->em->flush();
        } catch (UniqueConstraintViolationException $error) {
            throw new TurnUnavailable('Ese turno lo acaban de reservar. Elegí otro horario.', previous: $error);
        }

        return $turn;
    }

    public function changeStatus(Turn $turn, TurnStatus $to, User $by): void
    {
        $this->assertOwns($turn, $by);

        if ($turn->getStatus() === $to) {
            return;
        }

        if (!in_array($turn->getStatus(), [TurnStatus::Booked, TurnStatus::CheckedIn], true)) {
            throw new TurnUnavailable('El turno ya está en un estado final y no se puede cambiar.');
        }

        match ($to) {
            TurnStatus::CheckedIn => $turn->checkIn(),
            TurnStatus::Attended => $turn->markAttended(),
            TurnStatus::NoShow => $turn->markNoShow(),
            default => throw new TurnUnavailable('Ese cambio de estado no está permitido.'),
        };

        $this->em->flush();
    }

    public function cancel(Turn $turn, User $by, ?string $reason = null): void
    {
        $this->assertOwns($turn, $by);

        if ($turn->isCancelled()) {
            return;
        }

        if (!in_array($turn->getStatus(), [TurnStatus::Booked, TurnStatus::CheckedIn], true)) {
            throw new TurnUnavailable('Un turno ya atendido o marcado como ausente no se puede cancelar.');
        }

        $turn->cancel($by, $reason);
        $this->em->flush();
    }
}
