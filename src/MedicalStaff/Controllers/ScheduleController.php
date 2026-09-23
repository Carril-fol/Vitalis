<?php
namespace App\MedicalStaff\Controllers;

use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

use App\MedicalStaff\Forms\MedicalAbsenceType;
use App\MedicalStaff\Forms\MedicalScheduleType;
use App\MedicalStaff\Models\MedicalAbsence;
use App\MedicalStaff\Models\MedicalSchedule;
use App\MedicalStaff\Models\MedicalStaff;
use App\MedicalStaff\Services\ScheduleService;

#[Route('/medicals', name: 'medicals.')]
#[IsGranted('update_medics')]
class ScheduleController extends AbstractController
{
    public function __construct(
        private readonly ScheduleService $service,
    ) {
    }

    #[Route('/{id}/schedule', name: 'schedule', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function schedule(Request $request, #[MapEntity(id: 'id')] MedicalStaff $medic): Response
    {
        $schedule = new MedicalSchedule($medic);
        $scheduleForm = $this->createForm(MedicalScheduleType::class, $schedule);
        $scheduleForm->handleRequest($request);

        if ($scheduleForm->isSubmitted() && $scheduleForm->isValid()) {
            $conflict = $this->service->conflictMessage($schedule);

            if ($conflict === null) {
                $this->service->save($schedule);
                $this->addFlash('success', 'Horario agregado.');
                return $this->redirectToRoute('medicals.schedule', ['id' => $medic->getId()]);
            }

            $scheduleForm->addError(new FormError($conflict));
        }

        $absence = new MedicalAbsence($medic);
        $absenceForm = $this->createForm(MedicalAbsenceType::class, $absence);
        $absenceForm->handleRequest($request);

        if ($absenceForm->isSubmitted() && $absenceForm->isValid()) {
            $this->service->save($absence);
            $this->addFlash('success', 'Ausencia agregada.');
            return $this->redirectToRoute('medicals.schedule', ['id' => $medic->getId()]);
        }

        return $this->render('medicals/schedule.html.twig', [
            'medic' => $medic,
            'schedules' => $this->service->schedulesOf($medic),
            'absences' => $this->service->absencesOf($medic),
            'scheduleForm' => $scheduleForm,
            'absenceForm' => $absenceForm,
        ]);
    }

    #[Route('/holidays', name: 'holidays', methods: ['GET', 'POST'])]
    public function holidays(Request $request): Response
    {
        $holiday = new MedicalAbsence();
        $form = $this->createForm(MedicalAbsenceType::class, $holiday);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->service->save($holiday);
            $this->addFlash('success', 'Feriado agregado.');
            return $this->redirectToRoute('medicals.holidays');
        }

        return $this->render('medicals/holidays.html.twig', [
            'holidays' => $this->service->holidays(),
            'form' => $form,
        ]);
    }

    #[Route('/schedules/{id}/delete', name: 'schedule.delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function deleteSchedule(Request $request, #[MapEntity(id: 'id')] MedicalSchedule $schedule): Response
    {
        if (!$this->isCsrfTokenValid('delete-schedule-' . $schedule->getId(), $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $medicId = $schedule->getMedic()->getId();
        $this->service->remove($schedule);
        $this->addFlash('success', 'Horario quitado.');

        return $this->redirectToRoute('medicals.schedule', ['id' => $medicId]);
    }

    #[Route('/absences/{id}/delete', name: 'absence.delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function deleteAbsence(Request $request, #[MapEntity(id: 'id')] MedicalAbsence $absence): Response
    {
        if (!$this->isCsrfTokenValid('delete-absence-' . $absence->getId(), $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $medic = $absence->getMedic();
        $this->service->remove($absence);
        $this->addFlash('success', $medic === null ? 'Feriado quitado.' : 'Ausencia quitada.');

        return $medic === null
            ? $this->redirectToRoute('medicals.holidays')
            : $this->redirectToRoute('medicals.schedule', ['id' => $medic->getId()]);
    }
}
