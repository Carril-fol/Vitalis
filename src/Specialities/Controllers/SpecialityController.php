<?php

namespace App\Specialities\Controllers;

use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

use App\Specialities\Models\Speciality;
use App\Specialities\Forms\SpecialityType;
use App\Specialities\Services\SpecialityService;

#[Route('/specialities', name: 'specialities.')]
class SpecialityController extends AbstractController
{
    public function __construct(
        private readonly SpecialityService $service,
    ) {
    }

    private function save(Request $request, Speciality $speciality, string $template): Response
    {
        $isNew = $speciality->getId() === null;

        $form = $this->createForm(SpecialityType::class, $speciality);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->service->save($speciality);

            $this->addFlash('success', $isNew
                ? 'Especialidad creada.'
                : 'Los cambios de la especialidad se guardaron.');

            return $this->redirectToRoute('specialities.index');
        }

        return $this->render($template, ['form' => $form]);
    }

    #[Route('', name: 'index', methods: ['GET'])]
    #[IsGranted('read_specialities')]
    public function index(): Response
    {
        return $this->render('specialities/index.html.twig', [
            'specialities' => $this->service->findAll(),
        ]);
    }

    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
    #[IsGranted('create_specialities')]
    public function create(Request $request): Response
    {
        return $this->save($request, new Speciality(), 'specialities/create.html.twig');
    }

    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    #[IsGranted('update_specialities')]
    public function edit(Request $request, #[MapEntity(id: 'id')] Speciality $speciality): Response
    {
        return $this->save($request, $speciality, 'specialities/edit.html.twig');
    }

    #[Route('/{id}/delete', name: 'delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted('delete_specialities')]
    public function delete(Request $request, #[MapEntity(id: 'id')] Speciality $speciality): Response
    {
        if (!$this->isCsrfTokenValid('delete-speciality-' . $speciality->getId(), $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException();
        }

        if ($this->service->delete($speciality)) {
            $this->addFlash('success', 'Especialidad borrada.');
        } else {
            $this->addFlash('error', 'No se puede borrar la especialidad, ya que hay profesionales que la tienen asignada.');
        }

        return $this->redirectToRoute('specialities.index');
    }
}
