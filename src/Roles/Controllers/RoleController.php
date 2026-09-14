<?php
namespace App\Roles\Controllers;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use App\Core\ValidationException;
use App\Permissions\Interfaces\IPermissionService;
use App\Permissions\Models\Permission;
use App\Roles\Interfaces\IRoleService;
use App\Roles\Models\RoleArea;
use App\Roles\Schemas\CreateRole;
use App\Roles\Schemas\UpdateRole;


#[Route('/roles')]
class RoleController extends AbstractController
{
    private IRoleService $roleService;
    private IPermissionService $permissionService;

    public function __construct(IRoleService $roleService, IPermissionService $permissionService) {
        $this->roleService = $roleService;
        $this->permissionService = $permissionService;
    }

    #[Route('', name: 'roles.index', methods: array('GET'))]
    public function indexAction(): Response {
        return $this->render('roles/index.html.twig', array(
            'roles' => $this->roleService->getAllRoles(),
        ));
    }

    #[Route('/create', name: 'roles.create', methods: array('GET', 'POST'))]
    public function create(Request $request): Response {
        $schema = new CreateRole();
        $errors = array();

        if ($request->isMethod('POST')) {
            $schema = CreateRole::fromPost($request->request->all());

            try {
                $this->roleService->createRole($schema);
                return $this->redirectToRoute('roles.index');
            } catch (ValidationException $e) {
                $errors = $e->errors();
            }
        }

        return $this->render('roles/create.html.twig', array(
            'errors'      => $errors,
            'old'         => $schema,
            'areas'       => RoleArea::cases(),
            'permissions' => $this->permissionGrid(),
        ), new Response(status: $errors ? 422 : 200));
    }

    #[Route('/{id}/edit', name: 'roles.edit', requirements: array('id' => '\d+'), methods: array('GET', 'POST'))]
    public function edit(Request $request, int $id): Response {
        // Si no existe, el service tira NotFoundException y Symfony responde 404.
        $role = $this->roleService->getRoleById($id);

        $schema = UpdateRole::fromPost(array(
            'name'        => $role->name(),
            'area'        => $role->area()?->value ?? '',
            'permissions' => $this->roleService->getPermissionIds($role),
        ));
        $errors = array();

        if ($request->isMethod('POST')) {
            $schema = UpdateRole::fromPost($request->request->all());

            try {
                $this->roleService->updateRole($id, $schema);
                return $this->redirectToRoute('roles.index');
            } catch (ValidationException $e) {
                $errors = $e->errors();
            }
        }

        return $this->render('roles/edit.html.twig', array(
            'role'        => $role,
            'errors'      => $errors,
            'old'         => $schema,
            'areas'       => RoleArea::cases(),
            'permissions' => $this->permissionGrid(),
        ), new Response(status: $errors ? 422 : 200));
    }

    #[Route('/{id}/delete', name: 'roles.delete', requirements: array('id' => '\d+'), methods: array('POST'))]
    public function delete(Request $request, int $id): Response {
        if (!$this->isCsrfTokenValid('delete-role-' . $id, $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException();
        }

        try {
            $this->roleService->deleteRole($id);
        } catch (ValidationException $e) {
            $this->addFlash('error', $e->firstMessage());
        }

        return $this->redirectToRoute('roles.index');
    }

    /**
     * El catalogo de permisos armado como grilla para los checkboxes: los
     * nombres son <accion>_<modulo> (create_administratives), asi que queda
     * modulo => accion => Permission. Uno sin "_" va a la fila '' ("Otros").
     *
     * @return array{actions: string[], modules: array<string, array<string, Permission>>}
     */
    private function permissionGrid(): array {
        $known   = array('read', 'create', 'update', 'delete');
        $actions = array();
        $modules = array();

        foreach ($this->permissionService->getAllPermissions() as $permission) {
            $parts  = explode('_', $permission->getName(), 2);
            $action = $parts[0];
            $module = $parts[1] ?? '';

            $modules[$module][$action] = $permission;
            $actions[$action] = true;
        }

        ksort($modules);

        // Primero las acciones conocidas en su orden, despues cualquier otra.
        $actions = array_merge(
            array_values(array_intersect($known, array_keys($actions))),
            array_values(array_diff(array_keys($actions), $known))
        );

        return array('actions' => $actions, 'modules' => $modules);
    }
}
