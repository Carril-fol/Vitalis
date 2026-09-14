<?php
namespace App\Administratives\Controllers;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use App\Core\ValidationException;

use App\Administratives\Interfaces\IAdministrativeService;
use App\Administratives\Schemas\AdministrativeSchema;
use App\Administratives\Schemas\AdministrativeRegistrationSchema;


#[Route('/administratives')]
class AdministrativeController extends AbstractController
{
    private IAdministrativeService $administrativeService;

    public function __construct(IAdministrativeService $administrativeService) {
        $this->administrativeService = $administrativeService;
    }

    #[Route('', name: 'administratives.index', methods: array('GET'))]
    public function index(): Response {
        return $this->render('administratives/index.html.twig', array(
            'administratives' => $this->administrativeService->findAll(),
        ));
    }

    #[Route('/create', name: 'administratives.create', methods: array('GET', 'POST'))]
    public function create(Request $request): Response {
        $old    = array();
        $errors = array();

        if ($request->isMethod('POST')) {
            $schema = AdministrativeRegistrationSchema::fromPost($request->request->all());
            $old = $request->request->all();
            unset($old['password']);

            try {
                $this->administrativeService->register($schema);
                return $this->redirectToRoute('administratives.index');
            } catch (ValidationException $e) {
                $errors = $e->errors();
            }
        }

        return $this->render('administratives/create.html.twig', array(
            'errors'    => $errors,
            'old'       => $old,
            'positions' => $this->administrativeService->getPositions(),
        ), new Response(status: $errors ? 422 : 200));
    }

    #[Route('/{id}/edit', name: 'administratives.edit', requirements: array('id' => '\d+'), methods: array('GET', 'POST'))]
    public function edit(Request $request, int $id): Response {
        $administrative = $this->administrativeService->getAdministrativeById($id);
        $schema = AdministrativeSchema::fromPost($administrative);
        $errors = array();

        $canChangePosition = $this->isGranted('administratives.change_position');

        if ($request->isMethod('POST')) {
            $schema = AdministrativeSchema::fromPost($request->request->all());

            if (!$canChangePosition) {
                $schema->roleId = $administrative['role_id'];
            }

            try {
                $this->administrativeService->update($id, $schema);
                return $this->redirectToRoute('administratives.index');
            } catch (ValidationException $e) {
                $errors = $e->errors();
            }
        }

        return $this->render('administratives/edit.html.twig', array(
            'administrative'    => $administrative,
            'errors'            => $errors,
            'old'               => $schema,
            'positions'         => $this->administrativeService->getPositions(),
            'canChangePosition' => $canChangePosition,
        ), new Response(status: $errors ? 422 : 200));
    }

    #[Route('/{id}/deactivate', name: 'administratives.deactivate', requirements: array('id' => '\d+'), methods: array('POST'))]
    public function deactivate(Request $request, int $id): Response {
        if (!$this->isCsrfTokenValid('status-administrative-' . $id, $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $this->administrativeService->deactivate($id);

        return $this->redirectToRoute('administratives.index');
    }

    #[Route('/{id}/activate', name: 'administratives.activate', requirements: array('id' => '\d+'), methods: array('POST'))]
    public function activate(Request $request, int $id): Response {
        if (!$this->isCsrfTokenValid('status-administrative-' . $id, $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $this->administrativeService->activate($id);
        return $this->redirectToRoute('administratives.index');
    }
}
