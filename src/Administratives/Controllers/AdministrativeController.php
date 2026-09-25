<?php
namespace App\Administratives\Controllers;

use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

use App\Administratives\Models\Administrative;
use App\Administratives\Forms\AdministrativeType;
use App\Administratives\Services\AdministrativeService;

#[Route('/administratives', name: 'administratives.')]
class AdministrativeController extends AbstractController
{

    public function __construct(
        private readonly AdministrativeService $service,
    ) {
    }

    private function save(Request $request, Administrative $administrative, string $template): Response
    {
        $isNew = $administrative->getId() === null;

        $form = $this->createForm(AdministrativeType::class, $administrative, [
            'with_password' => !$isNew,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->service->save($administrative, $isNew ? null : $form->get('user')->get('plainPassword')->getData());

            $this->addFlash('success', $isNew
                ? 'Administrativo registrado. Contraseña inicial: ' . $administrative->getUser()->initialPassword()
                : 'Los cambios del administrativo se guardaron.');

            return $this->redirectToRoute('administratives.index');
        }

        return $this->render($template, ['form' => $form]);
    }

    #[Route('', name: 'index', methods: array('GET'))]
    #[IsGranted('read_administratives')]
    public function index(): Response
    {
        return $this->render('administratives/index.html.twig', [
            'administratives' => $this->service->findAll(),
        ]);
    }

    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
    #[IsGranted('create_administratives')]
    public function create(Request $request): Response
    {
        return $this->save($request, new Administrative(), 'administratives/create.html.twig');
    }

    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    #[IsGranted('update_administratives')]
    public function edit(Request $request, #[MapEntity(id: 'id')] Administrative $administrative): Response
    {
        return $this->save($request, $administrative, 'administratives/edit.html.twig');
    }

    #[Route('/{id}/{status}', name: 'status', requirements: ['id' => '\d+', 'status' => 'activate|deactivate'], methods: ['POST'])]
    #[IsGranted('update_administratives')]
    public function status(Request $request, #[MapEntity(id: 'id')] Administrative $administrative, string $status): Response
    {
        if (!$this->isCsrfTokenValid('status-administrative-' . $administrative->getId(), $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $this->service->changeStatus($administrative, $status === 'activate');

        $this->addFlash('success', $status === 'activate'
            ? 'Administrativo reactivado.'
            : 'Administrativo dado de baja. Sus datos se conservan.');

        return $this->redirectToRoute('administratives.index');
    }
}
