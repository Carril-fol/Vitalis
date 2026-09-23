<?php
namespace App\MedicalStaff\Controllers;

use App\Consultations\Services\ConsultationService;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

use App\Dashboard\Services\DashboardService;
use App\MedicalStaff\Forms\MedicalAbsenceType;
use App\MedicalStaff\Models\MedicalAbsence;
use App\MedicalStaff\Services\MedicalStaffService;
use App\MedicalStaff\Services\ScheduleService;
use App\Turns\Services\TurnService;
use App\Users\Models\User;

class MedicDashboardController extends AbstractController
{
    public function __construct(
        private readonly DashboardService $service,
        private readonly MedicalStaffService $medics,
        private readonly ScheduleService $schedules,
        private readonly TurnService $turns,
        private readonly ConsultationService $consultationService,
    ) {
    }

    #[Route('/dashboard/medico', name: 'dashboard.medic', methods: ['GET'])]
    public function index(#[CurrentUser] User $user): Response
    {
        $medic = $this->medics->ofUser($user);
        
        if ($medic === null) {
            return $this->redirectToRoute('dashboard.index');
        }

        $turns = $this->service->turnsToday($medic);

        return $this->render('medicals/agenda.html.twig', [
            'medic' => $medic,
            'turns' => $turns,
            'noteIds' => $this->consultationService->turnIdsWithNote($turns),
        ]);
    }

    #[Route('/dashboard/medico/horarios', name: 'dashboard.medic.schedule', methods: ['GET', 'POST'])]
    public function schedule(Request $request, #[CurrentUser] User $user): Response
    {
        $medic = $this->medics->ofUser($user);

        if ($medic === null) {
            return $this->redirectToRoute('dashboard.index');
        }

        $absence = new MedicalAbsence($medic);
        $form = $this->createForm(MedicalAbsenceType::class, $absence);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->schedules->save($absence);
            $this->addFlash('success', 'Ausencia cargada. Ya no se van a ofrecer turnos en ese rango.');

            $affected = $this->turns->countBetween($medic, $absence->getStartsAt(), $absence->getEndsAt());

            if ($affected > 0) {
                $this->addFlash('warning', sprintf(
                    $affected === 1
                    ? 'Ojo: hay 1 turno ya dado adentro de ese rango. Avisá en recepción para reprogramarlo.'
                    : 'Ojo: hay %d turnos ya dados adentro de ese rango. Avisá en recepción para reprogramarlos.',
                    $affected,
                ));
            }

            return $this->redirectToRoute('dashboard.medic.schedule');
        }

        return $this->render('medicals/my-schedule.html.twig', [
            'medic' => $medic,
            'schedules' => $this->schedules->schedulesOf($medic),
            'absences' => $this->schedules->absencesOf($medic),
            'holidays' => $this->schedules->holidays(),
            'form' => $form,
        ]);
    }

    #[Route(
        '/dashboard/medico/ausencias/{id}/delete',
        name: 'dashboard.medic.absence.delete',
        requirements: ['id' => '\d+'],
        methods: ['POST'],
    )]
    public function deleteAbsence(
        Request $request,
        #[MapEntity(id: 'id')] MedicalAbsence $absence,
        #[CurrentUser] User $user,
    ): Response {
        if (!$this->isCsrfTokenValid('delete-absence-' . $absence->getId(), $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $medic = $this->medics->ofUser($user);

        if ($medic === null) {
            return $this->redirectToRoute('dashboard.index');
        }

        $this->schedules->removeOwnAbsence($absence, $medic);
        $this->addFlash('success', 'Ausencia quitada.');

        return $this->redirectToRoute('dashboard.medic.schedule');
    }
}
