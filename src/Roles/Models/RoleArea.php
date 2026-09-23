<?php
namespace App\Roles\Models;

enum RoleArea: string
{
    case Administrative = 'ADMINISTRATIVE';
    case Medic = 'MEDIC';
    case Patient = 'PATIENT';

    public function label(): string
    {
        return match ($this) {
            self::Administrative => 'Administrativa',
            self::Medic => 'Medica',
            self::Patient => 'Pacientes',
        };
    }
}
