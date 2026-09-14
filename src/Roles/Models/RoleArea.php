<?php
namespace App\Roles\Models;

/**
 * El area a la que pertenece un rol. Varios roles pueden compartir area
 * ("Administrativo" y "Facturacion" son los dos administrativos), y los
 * formularios de alta de cada perfil ofrecen solo los roles de su area.
 *
 * roles.name se puede renombrar desde la pantalla de Roles; el area es lo que
 * compara el codigo, asi que renombrar un rol es seguro.
 *
 * La columna es nullable: un rol sin area (por ejemplo "Cadete") no aparece en
 * ningun alta de perfil, pero sirve igual para agrupar permisos.
 */
enum RoleArea: string
{
    case Administrative = 'ADMINISTRATIVE';
    case Medic = 'MEDIC';
    case Nurse = 'NURSE';
    case Patient = 'PATIENT';

    /** Como se muestra en los formularios y listados. */
    public function label(): string
    {
        return match ($this) {
            self::Administrative => 'Administrativa',
            self::Medic => 'Medica',
            self::Nurse => 'Enfermeria',
            self::Patient => 'Pacientes',
        };
    }
}
