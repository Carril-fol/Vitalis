<?php
namespace App\MedicalStaff\Schemas;

use App\Users\Schemas\UserUpdateSchema;
use Symfony\Component\Validator\Constraints as Assert;
use App\MedicalStaff\Models\MedicalStaff;

class MedicalStaffUpdateSchema extends MedicalStaffSchema
{
    #[Assert\NotNull(message: 'Faltan los datos de la persona')]
    #[Assert\Valid]
    public ?UserUpdateSchema $user = null;

    public static function fromEntity(MedicalStaff $medical): self
    {
        $schema = new self();
        $schema->roleId = $medical->user()->role()->id();
        $schema->specialityId = $medical->speciality?->id();
        $schema->hasLicense = $medical->licenseNumber() !== null;
        $schema->licenseNumber = $medical->licenseNumber();

        $schema->user = UserUpdateSchema::fromEntity($medical->user());
        return $schema;
    }
}