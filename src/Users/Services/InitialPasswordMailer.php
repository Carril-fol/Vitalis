<?php
namespace App\Users\Services;

use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;

use App\Users\Models\User;

class InitialPasswordMailer
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function send(User $user): void
    {
        try {
            $this->mailer->send((new TemplatedEmail())
                ->to($user->email())
                ->subject('Tu cuenta en Vitalis')
                ->htmlTemplate('emails/initial_password.html.twig')
                ->context(['user' => $user]));
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('No se pudo enviar la contraseña inicial', ['user' => $user->id(), 'error' => $e->getMessage()]);
        }
    }
}
