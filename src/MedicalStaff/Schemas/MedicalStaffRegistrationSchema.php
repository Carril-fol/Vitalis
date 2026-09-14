<?php

namespace App\MedicalStaff\Schemas;

use App\Users\Schemas\UserSchema;
use Symfony\Component\Validator\Constraints as Assert;


class MedicalStaffRegistrationSchema extends MedicalStaffSchema
{
    #[Assert\NotNull(message: 'Faltan los datos de la persona')]
    #[Assert\Valid]
    public ?UserSchema $user = null;
}