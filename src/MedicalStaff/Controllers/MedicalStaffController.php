<?php
namespace App\MedicalStaff\Controllers;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use App\Specialities\Interfaces\ISpecialityService;

use App\MedicalStaff\Interfaces\IMedicalStaffService;
use App\MedicalStaff\Forms\MedicalStaffRegistrationType;
use App\MedicalStaff\Forms\MedicalStaffUpdateType;


#[Route('/medicals', name: 'medicals.')]
class MedicalStaffController extends AbstractController
{
    public function __construct(
        private readonly IMedicalStaffService $service,
        private readonly ISpecialityService $specialityService,
    ) {
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('medicals/index.html.twig', [
            'medicalStaff' => $this->service->getAllMedicalStaff(),
        ]);
    }

    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $schema = $this->service->getMedicalStaffRegistrationSchema();

        $form = $this->createForm(
            MedicalStaffRegistrationType::class,
            $schema,
            $this->getFormOptions()
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->service->register($schema);
            return $this->redirectToRoute('medicals.index');
        }

        return $this->render(
            'medicals/create.html.twig',
            ['form' => $form->createView()],
            $this->getResponse($form)
        );
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request): Response
    {
        $schema = $this->service->getMedicalStaffUpdateSchemaById($id);

        $form = $this->createForm(
            MedicalStaffUpdateType::class,
            $schema,
            $this->getFormOptions()
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->service->update($id, $schema);
            return $this->redirectToRoute('medicals.index', ['id' => $id]);
        }

        return $this->render('medicals/edit.html.twig', [
            'form' => $form->createView(),
            'id' => $id
        ], $this->getResponse($form));
    }

    #[Route('/{id}/activate', name: 'activate', methods: ['POST'])]
    public function activate(int $id, Request $request): Response // <--- Agregamos Request
    {
        if (!$this->isCsrfTokenValid('status-medicalstaff-' . $id, $request->request->get('_token'))) {
            return new Response('Invalid CSRF token', Response::HTTP_FORBIDDEN);
        }

        $this->service->activate($id);
        return $this->redirectToRoute('medicals.index', ['id' => $id]);
    }

    #[Route('/{id}/deactivate', name: 'deactivate', methods: ['POST'])]
    public function deactivate(int $id, Request $request): Response // <--- Agregamos Request
    {
        if (!$this->isCsrfTokenValid('status-medicalstaff-' . $id, $request->request->get('_token'))) {
            return new Response('Invalid CSRF token', Response::HTTP_FORBIDDEN);
        }

        $this->service->deactivate($id);
        return $this->redirectToRoute('medicals.index', ['id' => $id]);
    }

    /**
     * Get form options with positions and specialities
     */
    private function getFormOptions(): array
    {
        return [
            'positions' => $this->service->getPositions(),
            'specialities' => $this->specialityService->getAllSpecialities(),
        ];
    }

    /**
     * Get response with appropriate status code for form validation
     */
    private function getResponse($form): Response
    {
        $status = ($form->isSubmitted() && !$form->isValid()) ? 422 : 200;
        return new Response(status: $status);
    }
}