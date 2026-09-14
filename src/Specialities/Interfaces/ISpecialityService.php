<?php
namespace App\Specialities\Interfaces;

use App\Core\NotFoundException;
use App\Core\ValidationException;

use App\Specialities\Models\Speciality;
use App\Specialities\Schemas\CreateSpecialitySchema;
use App\Specialities\Schemas\UpdateSpecialitySchema;


interface ISpecialityService
{
    /** @return Speciality[] */
    public function findAll(): array;

    /**
     * @return array<string, mixed>
     * @throws NotFoundException
     */
    public function getSpecialityById(int $id): array;

    public function getSpecialityModelById(int $id): ?Speciality;

    /** @throws ValidationException */
    public function createSpeciality(CreateSpecialitySchema $schema): int;

    /**
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function updateSpeciality(int $id, UpdateSpecialitySchema $schema): void;

    /** @throws NotFoundException */
    public function deleteSpeciality(int $id): void;

    public function getAllSpecialities();
}