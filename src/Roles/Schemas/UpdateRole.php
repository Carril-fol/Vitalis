<?php
namespace App\Roles\Schemas;


/**
 * Editar un rol pide los mismos datos que crearlo. Es una clase aparte para que
 * RoleService::updateRole() no acepte un CreateRole, y para poder separarlas
 * si algun dia cambian.
 */
class UpdateRole extends CreateRole
{
}
