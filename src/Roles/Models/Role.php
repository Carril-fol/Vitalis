<?php
namespace App\Roles\Models;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'roles')]
class Role
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    private string $name;

    #[ORM\Column(length: 30, nullable: true, enumType: RoleArea::class)]
    private ?RoleArea $area = null;

    public function __construct(string $name, ?RoleArea $area = null)
    {
        $this->name = trim($name);
        $this->area = $area;
    }

    public function area(): ?RoleArea
    {
        return $this->area;
    }

    public function belongsTo(RoleArea $area): bool
    {
        return $this->area === $area;
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = trim($name);
    }

    public function setArea(?RoleArea $area): void
    {
        $this->area = $area;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'area' => $this->area?->value,
        ];
    }
}
