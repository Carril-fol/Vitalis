<?php
namespace App\Consultations\Services;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use App\Consultations\Models\Consultation;
use App\MedicalStaff\Services\MedicalStaffService;
use App\Turns\Models\Turn;
use App\Turns\Models\TurnStatus;
use App\Turns\Services\TurnUnavailable;
use App\Users\Models\User;

class ConsultationService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly MedicalStaffService $medics,
    ) {
    }

    public function ofTurn(Turn $turn): ?Consultation
    {
        return $this->em->getRepository(Consultation::class)->findOneBy(['turn' => $turn]);
    }

    public function assertIsAttendingMedic(Turn $turn, User $user): void
    {
        $medic = $this->medics->ofUser($user);

        if ($medic === null || $turn->getMedicalStaff()?->getId() !== $medic->getId()) {
            throw new AccessDeniedException('La nota de la consulta es del profesional que atendió.');
        }
    }

    public function canWrite(Turn $turn): bool
    {
        return in_array($turn->getStatus(), [TurnStatus::CheckedIn, TurnStatus::Attended], true);
    }

    public function save(Consultation $note): void
    {
        $turn = $note->getTurn();

        if ($turn === null || !$this->canWrite($turn)) {
            throw new TurnUnavailable('Ese turno no está en condiciones de recibir una nota.');
        }

        if ($note->getId() !== null) {
            $note->markEdited();
        }

        $this->em->persist($note);
        $this->em->flush();
    }

    /**
     * @param Turn[] $turns
     * @return int[]
     */
    public function turnIdsWithNote(array $turns): array
    {
        if ($turns === []) {
            return [];
        }

        $turnsId = [];
        foreach ($turns as $turn) {
            $turnsId[] = $turn->getId();
        }

        $ids = $this->em->getRepository(Consultation::class)
            ->createQueryBuilder('c')
            ->select('IDENTITY(c.turn)')
            ->where('c.turn IN (:ids)')
            ->setParameter('ids', $turnsId)
            ->getQuery()
            ->getSingleColumnResult();

        return $ids;
    }

    /** @return Consultation[] */
    public function historyOf(Turn $turn): array
    {
        return $this->em->getRepository(Consultation::class)
            ->createQueryBuilder('c')
            ->join('c.turn', 't')->addSelect('t')
            ->join('c.createdBy', 'u')->addSelect('u')
            ->join('t.medicalStaff', 'm')->addSelect('m')
            ->leftJoin('m.speciality', 's')->addSelect('s')
            ->where('t.patient = :patient')
            ->andWhere('t.startsAt < :startsAt')
            ->setParameter('patient', $turn->getPatient())
            ->setParameter('startsAt', $turn->getStartsAt())
            ->orderBy('t.startsAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
