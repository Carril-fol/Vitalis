<?php
namespace App\HealthInsurances\Models;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity]
#[ORM\Table(name: 'health_insurances')]
#[UniqueEntity('name', message: "Ya existe una obra social con ese nombre.")]
class HealthInsurance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;


    #[ORM\Column(length: 100, unique: true)]
    #[Assert\NotBlank(message: 'El nombre es obligatorio')]
    #[Assert\Length(max: 100, maxMessage: 'El nombre no puede superar los {{ limit }} caracteres')]
    private string $name = '';

    #[ORM\Column(name: 'is_active')]
    private bool $active = true;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function activate(): void
    {
        $this->active = true;
    }

    public function deactivate(): void
    {
        $this->active = false;
    }

    public function setName(?string $name): void
    {
        $this->name = (string) $name;
    }
}