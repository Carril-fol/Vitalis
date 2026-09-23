<?php
namespace App\Auth\Controllers;

use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AuthController extends AbstractController
{
    #[Route('/login', name: 'login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('roles.index');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $errors = array();

        if ($error instanceof CustomUserMessageAccountStatusException) {
            $errors['credentials'] = array($error->getMessageKey());
        } elseif ($error) {
            $errors['credentials'] = array('Email o contraseña incorrectos');
        }

        return $this->render('auth/login.html.twig', array(
            'errors' => $errors,
            'old' => array('email' => $authenticationUtils->getLastUsername()),
        ));
    }

    #[Route('/logout', name: 'logout', methods: array('POST'))]
    public function logout(): void
    {
        throw new \LogicException('Logout lo maneja el firewall.');
    }
}