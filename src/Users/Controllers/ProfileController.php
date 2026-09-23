<?php
namespace App\Users\Controllers;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

use App\Users\Forms\ProfileType;
use App\Users\Models\User;
use App\Users\Services\ProfileService;

#[Route('/perfil', name: 'profile.')]
class ProfileController extends AbstractController
{
    public function __construct(
        private readonly ProfileService $service,
        private readonly Security $security,
    ) {
    }

    #[Route('', name: 'show', methods: ['GET'])]
    public function show(#[CurrentUser] User $user): Response
    {
        return $this->render('users/profile.html.twig', ['user' => $user]);
    }

    #[Route('/editar', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, #[CurrentUser] User $user): Response
    {
        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newPassword = $form->get('newPassword')->getData() ?: null;

            $this->service->save($user, $newPassword);

            if ($newPassword !== null) {
                $this->security->login($user);
            }

            $this->addFlash('success', $newPassword === null
                ? 'Tus datos se actualizaron.'
                : 'Tus datos y tu contraseña se actualizaron.');

            return $this->redirectToRoute('profile.show');
        }

        return $this->render('users/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }
}
