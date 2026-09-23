<?php
namespace App\ConsultingRooms\Services;

use Doctrine\ORM\EntityManagerInterface;

use App\ConsultingRooms\Models\ConsultingRoom;

class ConsultingRoomService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    /** @return ConsultingRoom[] */
    public function findAll(): array
    {
        return $this->em->getRepository(ConsultingRoom::class)->findBy([], ['name' => 'ASC']);
    }

    public function save(ConsultingRoom $room): void
    {
        $this->em->persist($room);
        $this->em->flush();
    }

    public function changeStatus(ConsultingRoom $room, bool $active): void
    {
        $active ? $room->activate() : $room->deactivate();
        $this->em->flush();
    }
}
