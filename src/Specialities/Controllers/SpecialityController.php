<?php

namespace App\Specialities\Controllers;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use App\Core\ValidationException;

use App\Specialities\Interfaces\ISpecialityService;
use App\Specialities\Schemas\CreateSpecialitySchema;
use App\Specialities\Schemas\UpdateSpecialitySchema;


#[Route('/specialities')]
class SpecialityController extends AbstractController
{
    public function __construct(
        private readonly ISpecialityService $specialityService,
    ) {}

    #[Route('', name: 'specialities.index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('specialities/index.html.twig', [
            'specialities' => $this->specialityService->findAll(),
        ]);
    }

    #[Route('/create', name: 'specialities.create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $schema = new CreateSpecialitySchema();
        $errors = [];

        if ($request->isMethod('POST')) {
            $schema = CreateSpecialitySchema::fromPost($request->request->all());

            try {
                $this->specialityService->createSpeciality($schema);

                return $this->redirectToRoute('specialities.index');
            } catch (ValidationException $e) {
                $errors = $e->errors();
            }
        }

        return $this->render('specialities/create.html.twig', [
            'errors' => $errors,
            'old'    => $schema,
        ], new Response(status: $errors ? 422 : 200));
    }

    #[Route('/{id}/edit', name: 'specialities.edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response
    {
        $speciality = $this->specialityService->getSpecialityById($id);
        $schema     = UpdateSpecialitySchema::fromPost($speciality);
        $errors     = [];

        if ($request->isMethod('POST')) {
            $schema = UpdateSpecialitySchema::fromPost($request->request->all());

            try {
                $this->specialityService->updateSpeciality($id, $schema);
                return $this->redirectToRoute('specialities.index');
            } catch (ValidationException $e) {
                $errors = $e->errors();
            }
        }

        return $this->render('specialities/edit.html.twig', [
            'speciality' => $speciality,
            'errors'     => $errors,
            'old'        => $schema,
        ], new Response(status: $errors ? 422 : 200));
    }

    #[Route('/{id}/delete', name: 'specialities.delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, int $id): Response
    {
        if (!$this->isCsrfTokenValid('delete-speciality-' . $id, $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $this->specialityService->deleteSpeciality($id);

        return $this->redirectToRoute('specialities.index');
    }
}