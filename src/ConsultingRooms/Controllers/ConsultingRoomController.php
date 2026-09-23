<?php
namespace App\ConsultingRooms\Controllers;

use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

use App\ConsultingRooms\Forms\ConsultingRoomType;
use App\ConsultingRooms\Models\ConsultingRoom;
use App\ConsultingRooms\Services\ConsultingRoomService;

#[Route('/consulting-rooms', name: 'consulting-rooms.')]
class ConsultingRoomController extends AbstractController
{

    public function __construct(
        private readonly ConsultingRoomService $service,
    ) {
    }

    private function save(Request $request, ConsultingRoom $room, string $template): Response
    {
        $isNew = $room->getId() === null;

        $form = $this->createForm(ConsultingRoomType::class, $room);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->service->save($room);

            $this->addFlash('success', $isNew
                ? 'Consultorio creado.'
                : 'Los cambios del consultorio se guardaron.');

            return $this->redirectToRoute('consulting-rooms.index');
        }

        return $this->render($template, ['form' => $form]);
    }

    #[Route('', name: 'index', methods: ['GET'])]
    #[IsGranted('read_consulting_rooms')]
    public function index(): Response
    {
        return $this->render('consulting-rooms/index.html.twig', [
            'rooms' => $this->service->findAll(),
        ]);
    }

    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
    #[IsGranted('create_consulting_rooms')]
    public function create(Request $request): Response
    {
        return $this->save($request, new ConsultingRoom(), 'consulting-rooms/create.html.twig');
    }

    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    #[IsGranted('update_consulting_rooms')]
    public function edit(Request $request, #[MapEntity(id: 'id')] ConsultingRoom $room): Response
    {
        return $this->save($request, $room, 'consulting-rooms/edit.html.twig');
    }

    #[Route('/{id}/{status}', name: 'status', requirements: ['id' => '\d+', 'status' => 'activate|deactivate'], methods: ['POST'])]
    #[IsGranted('update_consulting_rooms')]
    public function status(Request $request, #[MapEntity(id: 'id')] ConsultingRoom $room, string $status): Response
    {
        if (!$this->isCsrfTokenValid('status-consulting-room-' . $room->getId(), $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $this->service->changeStatus($room, $status === 'activate');

        $this->addFlash('success', $status === 'activate'
            ? 'Consultorio reactivado.'
            : 'Consultorio dado de baja.');

        return $this->redirectToRoute('consulting-rooms.index');
    }
}
