<?php

namespace App\Administratives\Interfaces;

use App\Core\NotFoundException;
use App\Core\ValidationException;

use App\Roles\Models\Role;
use App\Administratives\Models\Administrative;
use App\Administratives\Schemas\AdministrativeSchema;
use App\Administratives\Schemas\AdministrativeRegistrationSchema;

interface IAdministrativeService
{
    /**
     * @return array<string, mixed>
     * @throws NotFoundException
     */
    public function getAdministrativeById(int $id): array;

    /** @return array<string, mixed>|null */
    public function findByUserId(int $userId): ?array;

    /** @return Administrative[] */
    public function findByName(string $name): array;

    /** @return Administrative[] */
    public function findAll(): array;

    /** @throws ValidationException */
    public function register(AdministrativeRegistrationSchema $schema): int;

    /**
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function update(int $id, AdministrativeSchema $schema): void;

    /** @throws NotFoundException */
    public function activate(int $id): void;

    /** @throws NotFoundException */
    public function deactivate(int $id): void;
}