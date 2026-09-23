<?php
namespace App\Turns\Models;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use DateTimeImmutable;

use App\Turns\Models\TurnStatus;
use App\Patients\Models\Patient;
use App\MedicalStaff\Models\MedicalStaff;
use App\ConsultingRooms\Models\ConsultingRoom;
use App\Users\Models\User;


#[ORM\Entity]
#[ORM\Table(name: 'turns')]
#[ORM\UniqueConstraint(name: 'medic_slot', columns: ['medical_staff_id', 'starts_at', 'active_slot'])]
class Turn
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Patient::class)]
    #[ORM\JoinColumn(name: 'patient_id', nullable: false)]
    #[Assert\NotNull(message: 'Elegí un paciente')]
    private ?Patient $patient = null;

    #[ORM\ManyToOne(targetEntity: MedicalStaff::class)]
    #[ORM\JoinColumn(name: 'medical_staff_id', nullable: false)]
    #[Assert\NotNull(message: 'Elegí un profesional')]
    private ?MedicalStaff $medicalStaff = null;

    #[ORM\ManyToOne(targetEntity: ConsultingRoom::class)]
    #[ORM\JoinColumn(name: 'consulting_room_id', nullable: false)]
    #[Assert\NotNull(message: 'Elegí un consultorio')]
    private ?ConsultingRoom $consultingRoom = null;

    #[ORM\Column(name: 'starts_at', type: 'datetime_immutable')]
    #[Assert\NotNull(message: 'La fecha y hora del turno es obligatoria')]
    private ?DateTimeImmutable $startsAt = null;

    #[ORM\Column(name: 'ends_at', type: 'datetime_immutable')]
    #[Assert\NotNull(message: 'La hora de fin es obligatoria')]
    #[Assert\GreaterThan(propertyPath: 'startsAt', message: 'El turno tiene que terminar después de empezar')]
    private ?DateTimeImmutable $endsAt = null;

    #[ORM\Column(length: 20, enumType: TurnStatus::class)]
    private TurnStatus $status = TurnStatus::Booked;

    #[ORM\Column(name: 'active_slot', type: 'boolean', nullable: true)]
    private ?bool $activeSlot = true;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255, maxMessage: 'El motivo no puede superar los {{ limit }} caracteres')]
    private ?string $reason = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'created_by_id', nullable: false)]
    #[Assert\NotNull]
    private ?User $createdBy = null;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'cancelled_at', type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $cancelledAt = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'cancelled_by_id', nullable: true)]
    private ?User $cancelledBy = null;

    #[ORM\Column(name: 'cancellation_reason', length: 255, nullable: true)]
    #[Assert\Length(max: 255, maxMessage: 'El motivo de cancelación no puede superar los {{ limit }} caracteres')]
    private ?string $cancellationReason = null;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
    }


    public function checkIn(): void
    {
        $this->status = TurnStatus::CheckedIn;
    }

    public function markAttended(): void
    {
        $this->status = TurnStatus::Attended;
    }

    public function markNoShow(): void
    {
        $this->status = TurnStatus::NoShow;
    }

    public function cancel(User $by, ?string $reason = null): void
    {
        $this->status             = TurnStatus::Cancelled;
        $this->cancelledAt        = new DateTimeImmutable();
        $this->cancelledBy        = $by;
        $this->cancellationReason = $reason;
        $this->activeSlot         = null;
    }

    public function isCancelled(): bool
    {
        return $this->status === TurnStatus::Cancelled;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPatient(): ?Patient
    {
        return $this->patient;
    }

    public function getMedicalStaff(): ?MedicalStaff
    {
        return $this->medicalStaff;
    }

    public function getConsultingRoom(): ?ConsultingRoom
    {
        return $this->consultingRoom;
    }

    public function getStartsAt(): ?DateTimeImmutable
    {
        return $this->startsAt;
    }

    public function getEndsAt(): ?DateTimeImmutable
    {
        return $this->endsAt;
    }

    public function getStatus(): TurnStatus
    {
        return $this->status;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function getCancellationReason(): ?string
    {
        return $this->cancellationReason;
    }


    public function setPatient(?Patient $patient): void
    {
        $this->patient = $patient;
    }

    public function setMedicalStaff(?MedicalStaff $medicalStaff): void
    {
        $this->medicalStaff = $medicalStaff;
    }

    public function setConsultingRoom(?ConsultingRoom $consultingRoom): void
    {
        $this->consultingRoom = $consultingRoom;
    }

    public function setStartsAt(?DateTimeImmutable $startsAt): void
    {
        $this->startsAt = $startsAt;
    }

    public function setEndsAt(?DateTimeImmutable $endsAt): void
    {
        $this->endsAt = $endsAt;
    }

    public function setReason(?string $reason): void
    {
        $this->reason = $reason;
    }

    public function setCreatedBy(?User $createdBy): void
    {
        $this->createdBy = $createdBy;
    }
}
