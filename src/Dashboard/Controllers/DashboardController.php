<?php
namespace App\Dashboard\Controllers;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

use App\Dashboard\Services\DashboardService;
use App\MedicalStaff\Services\MedicalStaffService;
use App\Users\Models\User;

class DashboardController extends AbstractController
{
    public function __construct(
        private readonly DashboardService $service,
        private readonly MedicalStaffService $medics,
    ) {
    }

    #[Route('/dashboard', name: 'dashboard.index', methods: ['GET'])]
    public function index(#[CurrentUser] User $user): Response
    {
        if ($this->medics->ofUser($user) !== null) {
            return $this->redirectToRoute('dashboard.medic');
        }

        return $this->render('dashboard/index.html.twig', [
            'patients'     => $this->service->countPatient(),
            'todayCount'   => $this->service->countTurnToday(),
            'medics'       => $this->service->countMedicalStaff(),
            'specialities' => $this->service->countSpeciality(),
            'turns'        => $this->service->turnsToday(),
        ]);
    }
}
