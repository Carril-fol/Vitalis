<?php
namespace App\MedicalStaff\Models;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use App\ConsultingRooms\Models\ConsultingRoom;

#[ORM\Entity]
#[ORM\Table(name: 'medical_schedules')]
#[ORM\Index(name: 'medic_weekday', columns: ['medical_staff_id', 'weekday'])]
class MedicalSchedule
{
    public const WEEKDAYS = [
        1 => 'Lunes',
        2 => 'Martes',
        3 => 'Miércoles',
        4 => 'Jueves',
        5 => 'Viernes',
        6 => 'Sábado',
        7 => 'Domingo',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: MedicalStaff::class)]
    #[ORM\JoinColumn(name: 'medical_staff_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private MedicalStaff $medic;

    #[ORM\ManyToOne(targetEntity: ConsultingRoom::class)]
    #[ORM\JoinColumn(name: 'consulting_room_id', referencedColumnName: 'id', nullable: false)]
    #[Assert\NotNull(message: 'Elegí un consultorio')]
    private ?ConsultingRoom $consultingRoom = null;

    #[ORM\Column(type: 'smallint')]
    #[Assert\NotNull(message: 'Elegí un día')]
    #[Assert\Range(min: 1, max: 7, notInRangeMessage: 'Elegí un día de la semana')]
    private ?int $weekday = null;

    #[ORM\Column(name: 'start_time', type: 'time_immutable')]
    #[Assert\NotNull(message: 'La hora de inicio es obligatoria')]
    private ?DateTimeImmutable $startTime = null;

    #[ORM\Column(name: 'end_time', type: 'time_immutable')]
    #[Assert\NotNull(message: 'La hora de fin es obligatoria')]
    #[Assert\GreaterThan(propertyPath: 'startTime', message: 'La hora de fin tiene que ser posterior a la de inicio')]
    private ?DateTimeImmutable $endTime = null;

    #[ORM\Column(name: 'slot_minutes', type: 'smallint')]
    #[Assert\Range(min: 5, max: 240, notInRangeMessage: 'La duración tiene que estar entre {{ min }} y {{ max }} minutos')]
    private int $slotMinutes = 30;

    public function __construct(MedicalStaff $medic)
    {
        $this->medic = $medic;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMedic(): MedicalStaff
    {
        return $this->medic;
    }

    public function getConsultingRoom(): ?ConsultingRoom
    {
        return $this->consultingRoom;
    }

    public function getWeekday(): ?int
    {
        return $this->weekday;
    }

    public function getWeekdayName(): string
    {
        return self::WEEKDAYS[$this->weekday] ?? '';
    }

    public function getStartTime(): ?DateTimeImmutable
    {
        return $this->startTime;
    }

    public function getEndTime(): ?DateTimeImmutable
    {
        return $this->endTime;
    }

    public function getSlotMinutes(): int
    {
        return $this->slotMinutes;
    }

    public function setConsultingRoom(?ConsultingRoom $consultingRoom): void
    {
        $this->consultingRoom = $consultingRoom;
    }

    public function setWeekday(?int $weekday): void
    {
        $this->weekday = $weekday;
    }

    public function setStartTime(?DateTimeImmutable $startTime): void
    {
        $this->startTime = $startTime;
    }

    public function setEndTime(?DateTimeImmutable $endTime): void
    {
        $this->endTime = $endTime;
    }

    public function setSlotMinutes(?int $slotMinutes): void
    {
        $this->slotMinutes = (int) $slotMinutes;
    }
}
