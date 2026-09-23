<?php
namespace App\Administratives\Models;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use App\Users\Models\User;

#[ORM\Entity]
#[ORM\Table(name: 'administratives')]
class Administrative
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: User::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', unique: true, nullable: false, onDelete: 'CASCADE')]
    #[Assert\Valid]
    private User $user;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'El sector es obligatorio')]
    #[Assert\Length(max: 100, maxMessage: 'El sector no puede superar los {{ limit }} caracteres')]
    private string $sector = '';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    public function __construct()
    {
        $this->user = new User();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getSector(): string
    {
        return $this->sector;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }


    public function setSector(?string $sector): void
    {
        $this->sector = (string) $sector;
    }

    public function setNotes(?string $notes): void
    {
        $this->notes = $notes;
    }
}
