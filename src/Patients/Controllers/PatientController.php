<?php
namespace App\Patients\Controllers;

use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

use App\Patients\Models\Patient;
use App\Patients\Forms\PatientType;
use App\Patients\Services\PatientService;

#[Route('/patients', name: 'patients.')]
class PatientController extends AbstractController
{

    public function __construct(
        private readonly PatientService $service,
    ) {
    }

    private function save(Request $request, Patient $patient, string $template): Response
    {
        $isNew = $patient->getId() === null;

        $form = $this->createForm(PatientType::class, $patient, [
            'require_password' => $isNew,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->service->save($patient, $form->get('user')->get('plainPassword')->getData());

            $this->addFlash('success', $isNew
                ? 'Paciente registrado.'
                : 'Los cambios del paciente se guardaron.');

            return $this->redirectToRoute('patients.index');
        }

        return $this->render($template, ['form' => $form]);
    }

    #[Route('', name: 'index', methods: array('GET'))]
    #[IsGranted('read_patients')]
    public function index(): Response
    {
        return $this->render('patients/index.html.twig', [
            'patients' => $this->service->findAll(),
        ]);
    }

    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
    #[IsGranted('create_patients')]
    public function create(Request $request): Response
    {
        return $this->save($request, $this->service->newPatient(), 'patients/create.html.twig');
    }

    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    #[IsGranted('update_patients')]
    public function edit(Request $request, #[MapEntity(id: 'id')] Patient $patient): Response
    {
        return $this->save($request, $patient, 'patients/edit.html.twig');
    }

    #[Route('/{id}/{status}', name: 'status', requirements: ['id' => '\d+', 'status' => 'activate|deactivate'], methods: ['POST'])]
    #[IsGranted('update_patients')]
    public function status(Request $request, #[MapEntity(id: 'id')] Patient $patient, string $status): Response
    {
        if (!$this->isCsrfTokenValid('status-patient-' . $patient->getId(), $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $this->service->changeStatus($patient, $status === 'activate');

        $this->addFlash('success', $status === 'activate'
            ? 'Paciente reactivado.'
            : 'Paciente dado de baja. Sus datos se conservan.');

        return $this->redirectToRoute('patients.index');
    }
}
