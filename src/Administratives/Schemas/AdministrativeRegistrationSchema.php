<?php

namespace App\Administratives\Schemas;

use App\Users\Schemas\UserSchema;
use Symfony\Component\Validator\Constraints as Assert;


class AdministrativeRegistrationSchema extends AdministrativeSchema
{
    #[Assert\NotNull(message: 'Faltan los datos de la persona')]
    #[Assert\Valid]
    public ?UserSchema $user = null;

    protected function fill(array $post): void
    {
        parent::fill($post);

        $this->user = UserSchema::fromPost($post);
    }
}
