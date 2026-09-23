<?php
namespace App\Landing\Controllers;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use App\HealthInsurances\Services\HealthInsuranceService;

class LandingController extends AbstractController
{
    public function __construct(
        private readonly HealthInsuranceService $healthInsurances,
    ) {
    }

    #[Route('/', name: 'landing', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('landing/index.html.twig', [
            'healthInsurances' => array_filter($this->healthInsurances->findAll(), fn ($h) => $h->isActive()),
        ]);
    }
}
