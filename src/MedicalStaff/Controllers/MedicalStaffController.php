<?php
namespace App\MedicalStaff\Controllers;

use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

use App\MedicalStaff\Forms\MedicalStaffType;
use App\MedicalStaff\Models\MedicalStaff;
use App\MedicalStaff\Services\MedicalStaffService;

#[Route('/medicals', name: 'medicals.')]
class MedicalStaffController extends AbstractController
{
    public function __construct(
        private readonly MedicalStaffService $service,
    ) {
    }

    #[Route('', name: 'index', methods: ['GET'])]
    #[IsGranted('read_medics')]
    public function index(): Response
    {
        return $this->render('medicals/index.html.twig', [
            'medicalStaff' => $this->service->findAll(),
        ]);
    }

    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
    #[IsGranted('create_medics')]
    public function create(Request $request): Response
    {
        return $this->save($request, new MedicalStaff(), 'medicals/create.html.twig');
    }

    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    #[IsGranted('update_medics')]
    public function edit(Request $request, #[MapEntity(id: 'id')] MedicalStaff $medicalStaff): Response
    {
        return $this->save($request, $medicalStaff, 'medicals/edit.html.twig');
    }

    #[Route('/{id}/{status}', name: 'status', requirements: ['id' => '\d+', 'status' => 'activate|deactivate'], methods: ['POST'])]
    #[IsGranted('update_medics')]
    public function status(Request $request, #[MapEntity(id: 'id')] MedicalStaff $medicalStaff, string $status): Response
    {
        if (!$this->isCsrfTokenValid('status-medicalstaff-' . $medicalStaff->getId(), $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $this->service->changeStatus($medicalStaff, $status === 'activate');

        $this->addFlash('success', $status === 'activate'
            ? 'Profesional reactivado.'
            : 'Profesional dado de baja. Sus datos se conservan.');

        return $this->redirectToRoute('medicals.index');
    }

    private function save(Request $request, MedicalStaff $medicalStaff, string $template): Response
    {
        $isNew = $medicalStaff->getId() === null;

        $form = $this->createForm(MedicalStaffType::class, $medicalStaff, [
            'with_password' => !$isNew,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->service->save($medicalStaff, $isNew ? null : $form->get('user')->get('plainPassword')->getData());

            $this->addFlash('success', $isNew
                ? 'Profesional registrado. Contraseña inicial: ' . $medicalStaff->getUser()->initialPassword()
                : 'Los cambios del profesional se guardaron.');

            return $this->redirectToRoute('medicals.index');
        }

        return $this->render($template, ['form' => $form]);
    }
}
