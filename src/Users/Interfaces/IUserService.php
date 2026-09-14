<?php
namespace App\Users\Interfaces;

use App\Roles\Models\Role;
use App\Users\Models\User;
use App\Users\Schemas\UserSchema;
use App\Users\Schemas\UserUpdateSchema;


interface IUserService
{
    public function getUserByRole(Role $role): array;
    public function registerUser(UserSchema $schema, Role $role): User;

    public function updateUser(User $user, UserUpdateSchema $schema): void;
}