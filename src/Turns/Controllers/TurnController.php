<?php
namespace App\Turns\Controllers;

use DateTimeImmutable;

use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

use App\Consultations\Services\ConsultationService;
use App\MedicalStaff\Models\MedicalStaff;
use App\MedicalStaff\Services\MedicalStaffService;
use App\Patients\Models\Patient;
use App\Turns\Forms\TurnType;
use App\Turns\Models\Turn;
use App\Turns\Models\TurnStatus;
use App\Turns\Services\TurnService;
use App\Turns\Services\TurnUnavailable;
use App\Users\Models\User;

#[Route('/turns', name: 'turns.')]
class TurnController extends AbstractController
{
    public function __construct(
        private readonly TurnService $service,
        private readonly MedicalStaffService $medics,
        private readonly ConsultationService $consultations,
    ) {
    }

    #[Route('', name: 'index', methods: ['GET'])]
    #[IsGranted('read_turns')]
    public function index(Request $request, #[CurrentUser] User $user): Response
    {
        $medic = $this->medics->ofUser($user);
        $page = $request->query->getInt('page', 1);

        $turns = $medic !== null
            ? $this->service->findAllOfMedic($medic, $page)
            : $this->service->findAll($page);

        return $this->render('turns/index.html.twig', [
            'medic' => $medic,
            'turns' => $turns,
            'noteIds' => $medic !== null
                ? $this->consultations->turnIdsWithNote($turns->getItems())
                : [],
        ]);
    }

    #[Route('/create', name: 'create', methods: ['GET'])]
    #[IsGranted('create_turns')]
    public function create(Request $request): Response
    {
        $form = $this->createForm(TurnType::class);
        $form->handleRequest($request);

        $search = $form->isSubmitted() && $form->isValid() ? $form->getData() : null;

        return $this->render('turns/create.html.twig', [
            'form' => $form,
            'search' => $search,
            'slots' => $search ? $this->service->availableSlots($search['speciality']) : [],
        ]);
    }

    #[Route(
        '/{patient}/book/{medic}/{startsAt}',
        name: 'book',
        requirements: ['patient' => '\d+', 'medic' => '\d+', 'startsAt' => '[\d\-T:]+'],
        methods: ['POST'],
    )]
    #[IsGranted('create_turns')]
    public function book(
        Request $request,
        #[MapEntity(id: 'patient')] Patient $patient,
        #[MapEntity(id: 'medic')] MedicalStaff $medic,
        DateTimeImmutable $startsAt,
        #[CurrentUser] User $createdBy,
    ): Response {
        if (!$this->isCsrfTokenValid('book-turn', $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException();
        }

        try {
            $turn = $this->service->bookSlot(
                $patient,
                $medic,
                $startsAt,
                $createdBy,
                $request->request->getString('reason') ?: null,
            );

            $this->addFlash('success', sprintf(
                'Turno reservado para el %s con %s.',
                $turn->getStartsAt()->format('d/m/Y \a \l\a\s H:i'),
                $turn->getMedicalStaff()->getUser()->fullName(),
            ));
        } catch (TurnUnavailable $error) {
            $this->addFlash('error', $error->getMessage());
        }

        return $this->redirectToRoute('turns.index');
    }

    private function backTo(Request $request): RedirectResponse
    {
        if ($request->request->getString('back') === 'medic') {
            return $this->redirectToRoute('dashboard.medic');
        }

        return $this->redirectToRoute('turns.index', ['page' => $request->query->getInt('page', 1)]);
    }

    #[Route('/{id}/cancel', name: 'cancel', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted('update_turns')]
    public function cancel(
        Request $request,
        #[MapEntity(id: 'id')] Turn $turn,
        #[CurrentUser] User $by,
    ): Response {
        if (!$this->isCsrfTokenValid('cancel-turn-' . $turn->getId(), $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException();
        }

        try {
            $this->service->cancel($turn, $by);
            $this->addFlash('success', 'Turno cancelado. El horario vuelve a estar disponible.');
        } catch (TurnUnavailable $error) {
            $this->addFlash('error', $error->getMessage());
        }

        return $this->backTo($request);
    }

    #[Route(
        '/{id}/{status}',
        name: 'status',
        requirements: ['id' => '\d+', 'status' => 'checked_in|attended|no_show'],
        methods: ['POST'],
    )]
    #[IsGranted('update_turns')]
    public function status(
        Request $request,
        #[MapEntity(id: 'id')] Turn $turn,
        TurnStatus $status,
        #[CurrentUser] User $by,
    ): Response {
        if (!$this->isCsrfTokenValid('status-turn-' . $turn->getId(), $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException();
        }

        try {
            $this->service->changeStatus($turn, $status, $by);
            $this->addFlash('success', 'Estado del turno actualizado.');
        } catch (TurnUnavailable $error) {
            $this->addFlash('error', $error->getMessage());
        }

        return $this->backTo($request);
    }
}
