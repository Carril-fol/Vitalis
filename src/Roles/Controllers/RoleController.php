<?php
namespace App\Roles\Controllers;

use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

use App\Roles\Models\Role;
use App\Roles\Forms\RoleType;
use App\Roles\Services\RoleService;

#[Route('/roles', name: 'roles.')]
class RoleController extends AbstractController
{
    public function __construct(
        private readonly RoleService $service,
    ) {
    }

    private function save(Request $request, Role $role, string $template): Response
    {
        $form = $this->createForm(RoleType::class, $role);
        $isNew = $role->getId() === null;

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->service->save($role);

            $this->addFlash('success', $isNew
                ? 'Rol creado.'
                : 'Los cambios del rol se guardaron.');

            return $this->redirectToRoute('roles.index');
        }

        return $this->render($template, ['form' => $form]);
    }

    #[Route('', name: 'index', methods: array('GET'))]
    #[IsGranted('read_roles')]
    public function index(): Response
    {
        return $this->render('roles/index.html.twig', [
            'roles' => $this->service->findAll(),
        ]);
    }

    #[Route('/create', name: 'create', methods: array('GET', 'POST'))]
    #[IsGranted('create_roles')]
    public function create(Request $request): Response
    {
        return $this->save($request, new Role(), 'roles/create.html.twig');
    }

    #[Route('/{id}/edit', name: 'edit', requirements: array('id' => '\d+'), methods: array('GET', 'POST'))]
    #[IsGranted('update_roles')]
    public function edit(Request $request, #[MapEntity(id: 'id')] Role $role): Response
    {
        return $this->save($request, $role, 'roles/edit.html.twig');
    }

    #[Route('/{id}/delete', name: 'delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted('delete_roles')]
    public function delete(Request $request, #[MapEntity(id: 'id')] Role $role): Response
    {
        if (!$this->isCsrfTokenValid('delete-role-' . $role->getId(), $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException();
        }

        if ($this->service->delete($role)) {
            $this->addFlash('success', 'Rol borrado.');
        } else {
            $this->addFlash('error', 'No se puede borrar el rol, ya que hay usuarios que lo tienen asignado.');
        }

        return $this->redirectToRoute('roles.index');
    }
}
