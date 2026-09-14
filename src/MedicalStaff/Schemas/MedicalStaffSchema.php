<?php
namespace App\MedicalStaff\Schemas;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class MedicalStaffSchema
{
    #[Assert\NotNull(message: 'El puesto es obligatorio')]
    public ?int $roleId = null;

    public ?int $specialityId = null;

    public bool $hasLicense = false;

    #[Assert\Length(max: 20, maxMessage: 'La matrícula no puede superar los 20 caracteres')]
    public ?string $licenseNumber = null;

    public function validationGroups(): array
    {
        return array('Default');
    }

    #[Assert\Callback]
    public function validateLicense(ExecutionContextInterface $context): void
    {
        if ($this->hasLicense && trim((string) $this->licenseNumber) === '') {
            $context->buildViolation('La matrícula es obligatoria para este puesto')
                ->atPath('licenseNumber')
                ->addViolation();
        }
    }
}