<?php
namespace App\Consultations\Controllers;

use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

use App\Consultations\Forms\ConsultationType;
use App\Consultations\Models\Consultation;
use App\Consultations\Services\ConsultationService;
use App\Turns\Models\Turn;
use App\Turns\Services\TurnUnavailable;
use App\Users\Models\User;

#[Route('/consultations', name: 'consultations.')]
class ConsultationController extends AbstractController
{
    public function __construct(
        private readonly ConsultationService $service,
    ) {
    }

    #[Route('/{turn}', name: 'show', requirements: ['turn' => '\d+'], methods: ['GET', 'POST'])]
    public function show(
        Request $request,
        #[MapEntity(id: 'turn')] Turn $turn,
        #[CurrentUser] User $user,
    ): Response {
        $this->service->assertIsAttendingMedic($turn, $user);

        $note = $this->service->ofTurn($turn) ?? new Consultation($turn, $user);
        $form = $this->createForm(ConsultationType::class, $note);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $isNew = $note->getId() === null;
                $this->service->save($note);
                $this->addFlash('success', $isNew ? 'Nota guardada.' : 'Nota actualizada.');

                return $this->redirectToRoute('consultations.show', ['turn' => $turn->getId()]);
            } catch (TurnUnavailable $error) {
                $this->addFlash('error', $error->getMessage());
            }
        }

        return $this->render('consultations/show.html.twig', [
            'turn' => $turn,
            'note' => $note,
            'form' => $form,
            'canWrite' => $this->service->canWrite($turn),
            'history' => $this->service->historyOf($turn),
        ]);
    }
}
