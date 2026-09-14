<?php

namespace App\Administratives\Schemas;

use Symfony\Component\Validator\Constraints as Assert;


class AdministrativeSchema
{
    #[Assert\NotBlank(message: 'El sector es obligatorio')]
    #[Assert\Length(max: 100, maxMessage: 'El sector no puede superar los {{ limit }} caracteres')]
    public string $sector = '';

    /**
     * El puesto: el id de un rol con area ADMINISTRATIVE. Aca solo se exige que
     * venga; que sea de esa area lo chequea el service contra la base.
     */
    #[Assert\NotNull(message: 'El puesto es obligatorio')]
    public ?int $roleId = null;

    public ?string $notes = null;

    public static function fromPost(array $post): static
    {
        $schema = new static();
        $schema->fill($post);

        return $schema;
    }

    public function validationGroups(): array
    {
        return array('Default');
    }

    protected function fill(array $post): void
    {
        $this->sector = trim($post['sector'] ?? '');

        $roleId = trim((string) ($post['role_id'] ?? ''));
        $this->roleId = ctype_digit($roleId) ? (int) $roleId : null;

        $notes = trim($post['notes'] ?? '');
        $this->notes = $notes === '' ? null : $notes;
    }
}
