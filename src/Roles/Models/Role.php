<?php
namespace App\Roles\Models;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

use App\Permissions\Models\Permission;


#[ORM\Entity]
#[ORM\Table(name: 'roles')]
#[UniqueEntity('name', message: "Ya existe un rol con ese nombre.")]
class Role
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    #[Assert\NotBlank(message: 'El nombre es obligatorio')]
    #[Assert\Length(max: 50, maxMessage: 'El nombre no puede superar los {{ limit }} caracteres')]
    private string $name;

    #[ORM\Column(length: 30, nullable: true, enumType: RoleArea::class)]
    private ?RoleArea $area = null;

    #[ORM\ManyToMany(targetEntity: Permission::class)]
    #[ORM\JoinTable(name: 'roles_permissions')]
    private Collection $permissions;

    public function __construct()
    {
        $this->permissions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getArea(): ?RoleArea
    {
        return $this->area;
    }

    public function getPermissions(): Collection
    {
        return $this->permissions;
    }

    public function setName(?string $name): void
    {
        $this->name = (string) $name;
    }

    public function setArea(?RoleArea $area): void
    {
        $this->area = $area;
    }

    }
