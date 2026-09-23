<?php
namespace App\MedicalStaff\Models;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'medical_absences')]
#[ORM\Index(name: 'absence_medic_range', columns: ['medical_staff_id', 'starts_at'])]
class MedicalAbsence
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: MedicalStaff::class)]
    #[ORM\JoinColumn(name: 'medical_staff_id', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    private ?MedicalStaff $medic;

    #[ORM\Column(name: 'starts_at', type: 'datetime_immutable')]
    #[Assert\NotNull(message: 'La fecha de inicio es obligatoria')]
    private ?DateTimeImmutable $startsAt = null;

    #[ORM\Column(name: 'ends_at', type: 'datetime_immutable')]
    #[Assert\NotNull(message: 'La fecha de fin es obligatoria')]
    #[Assert\GreaterThan(propertyPath: 'startsAt', message: 'El fin tiene que ser posterior al inicio')]
    private ?DateTimeImmutable $endsAt = null;

    #[ORM\Column(length: 150, nullable: true)]
    #[Assert\Length(max: 150, maxMessage: 'El motivo no puede superar los {{ limit }} caracteres')]
    private ?string $reason = null;

    public function __construct(?MedicalStaff $medic = null)
    {
        $this->medic = $medic;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMedic(): ?MedicalStaff
    {
        return $this->medic;
    }

    public function getStartsAt(): ?DateTimeImmutable
    {
        return $this->startsAt;
    }

    public function getEndsAt(): ?DateTimeImmutable
    {
        return $this->endsAt;
    }

    public function getReason(): ?string
    {
        return $this->reason;
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
}
