<?php
namespace App\HealthInsurances\Controllers;

use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

use App\HealthInsurances\Forms\HealthInsuranceType;
use App\HealthInsurances\Models\HealthInsurance;
use App\HealthInsurances\Services\HealthInsuranceService;

#[Route('/health-insurances', name: 'health-insurances.')]
class HealthInsuranceController extends AbstractController
{

    public function __construct(
        private readonly HealthInsuranceService $service,
    ) {
    }

    private function save(Request $request, HealthInsurance $healthInsurance, string $template): Response
    {
        $isNew = $healthInsurance->getId() === null;

        $form = $this->createForm(HealthInsuranceType::class, $healthInsurance);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->service->save($healthInsurance);

            $this->addFlash('success', $isNew
                ? 'Obra social creada.'
                : 'Los cambios de la obra social se guardaron.');

            return $this->redirectToRoute('health-insurances.index');
        }

        return $this->render($template, ['form' => $form]);
    }

    #[Route('', name: 'index', methods: array('GET'))]
    #[IsGranted('read_health_insurances')]
    public function index(): Response
    {
        return $this->render('health-insurances/index.html.twig', [
            'healthInsurances' => $this->service->findAll(),
        ]);
    }

    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
    #[IsGranted('create_health_insurances')]
    public function create(Request $request): Response
    {
        return $this->save($request, new HealthInsurance(), 'health-insurances/create.html.twig');
    }

    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    #[IsGranted('update_health_insurances')]
    public function edit(Request $request, #[MapEntity(id: 'id')] HealthInsurance $healthInsurance): Response
    {
        return $this->save($request, $healthInsurance, 'health-insurances/edit.html.twig');
    }
}
