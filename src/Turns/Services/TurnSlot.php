<?php
namespace App\Turns\Services;

use DateTimeImmutable;

use App\ConsultingRooms\Models\ConsultingRoom;
use App\MedicalStaff\Models\MedicalStaff;

final readonly class TurnSlot
{
    public function __construct(
        public MedicalStaff $medic,
        public ConsultingRoom $room,
        public DateTimeImmutable $startsAt,
        public DateTimeImmutable $endsAt,
    ) {
    }
}
