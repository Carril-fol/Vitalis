<?php
namespace App\Administratives\Services;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

use App\Administratives\Models\Administrative;
use App\Users\Services\InitialPasswordMailer;

class AdministrativeService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly InitialPasswordMailer $initialPasswordMailer,
    ) {
    }

    /** @return Administrative[] */
    public function findAll(): array
    {
        return $this->em->createQueryBuilder()
            ->select('a', 'u', 'r')
            ->from(Administrative::class, 'a')
            ->join('a.user', 'u')
            ->join('u.role', 'r')
            ->orderBy('u.lastName')
            ->getQuery()
            ->getResult();
    }

    public function save(Administrative $administrative, ?string $plainPassword): void
    {
        $user = $administrative->getUser();
        $isNew = $user->id() === null;
        $plainPassword ??= $isNew ? $user->initialPassword() : null;

        if ($plainPassword) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));
        }

        $this->em->persist($administrative);
        $this->em->flush();

        if ($isNew) {
            $this->initialPasswordMailer->send($user);
        }
    }

    public function changeStatus(Administrative $administrative, bool $active): void
    {
        $active ? $administrative->getUser()->activate() : $administrative->getUser()->deactivate();
        $this->em->flush();
    }
}
