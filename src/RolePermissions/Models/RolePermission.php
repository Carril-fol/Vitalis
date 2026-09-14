<?php

namespace App\RolePermissions\Models;

use Doctrine\ORM\Mapping as ORM;
use App\Roles\Models\Role;
use App\Permissions\Models\Permission;

#[ORM\Entity]
#[ORM\Table(name: 'roles_permissions')]
#[ORM\UniqueConstraint(
    name: 'role_permission_unique',
    columns: ['role_id', 'permission_id']
)]
class RolePermission
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    // CASCADE: borrar un rol se lleva sus permisos; sin esto la FK lo impide.
    #[ORM\ManyToOne(targetEntity: Role::class)]
    #[ORM\JoinColumn(
        name: 'role_id',
        referencedColumnName: 'id',
        nullable: false,
        onDelete: 'CASCADE'
    )]
    private Role $role;

    #[ORM\ManyToOne(targetEntity: Permission::class)]
    #[ORM\JoinColumn(
        name: 'permission_id',
        referencedColumnName: 'id',
        nullable: false
    )]
    private Permission $permission;

    public function __construct(Role $role, Permission $permission)
    {
        $this->role = $role;
        $this->permission = $permission;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRole(): Role
    {
        return $this->role;
    }

    public function setRole(Role $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getPermission(): Permission
    {
        return $this->permission;
    }

    public function setPermission(Permission $permission): self
    {
        $this->permission = $permission;

        return $this;
    }
}