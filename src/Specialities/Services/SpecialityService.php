<?php

namespace App\Specialities\Services;

use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

use App\Core\NotFoundException;
use App\Core\ValidationException;

use App\Specialities\Models\Speciality;
use App\Specialities\Interfaces\ISpecialityService;
use App\Specialities\Repositories\SpecialityRepository;
use App\Specialities\Schemas\CreateSpecialitySchema;
use App\Specialities\Schemas\UpdateSpecialitySchema;
use Override;

class SpecialityService implements ISpecialityService
{
    public function __construct(
        private readonly SpecialityRepository $specialityRepository,
        private readonly ValidatorInterface $validator,
    ) {
    }

    public function findAll(): array
    {
        return $this->specialityRepository->findAll();
    }

    public function getSpecialityById(int $id): array
    {
        return $this->findOrFail($id)->toArray();
    }

    public function getSpecialityModelById(int $id): ?Speciality
    {
        return $this->specialityRepository->find($id);
    }

    public function createSpeciality(CreateSpecialitySchema $schema): int
    {
        $this->assertValid($schema);

        if ($this->specialityRepository->findByName($schema->name)) {
            throw ValidationException::forField('name', 'La especialidad ya existe.');
        }

        $speciality = new Speciality($schema->name);
        $this->save($speciality);

        return $speciality->getId();
    }

    public function updateSpeciality(int $id, UpdateSpecialitySchema $schema): void
    {
        $speciality = $this->findOrFail($id);
        $this->assertValid($schema);

        $existing = $this->specialityRepository->findByName($schema->name);
        if ($existing && $existing->id() !== $id) {
            throw ValidationException::forField('name', 'La especialidad ya existe.');
        }

        $speciality->setName($schema->name);
        $this->save($speciality);
    }

    public function deleteSpeciality(int $id): void
    {
        $this->specialityRepository->remove($this->findOrFail($id));
    }

    public function getAllSpecialities()
    {
        $specialities = $this->specialityRepository->findAll();
        $result = [];
        foreach ($specialities as $speciality) {
            $result[] = $speciality->toArray();
        }
        return $result;
    }

    private function findOrFail(int $id): Speciality
    {
        return $this->specialityRepository->find($id)
            ?? throw new NotFoundException('Speciality not found');
    }

    private function assertValid(CreateSpecialitySchema|UpdateSpecialitySchema $schema): void
    {
        $violations = $this->validator->validate($schema);

        if (count($violations) > 0) {
            throw ValidationException::fromViolations($violations);
        }
    }

    private function save(Speciality $speciality): void
    {
        try {
            $this->specialityRepository->save($speciality);
        } catch (UniqueConstraintViolationException $e) {
            throw ValidationException::forField('name', 'La especialidad ya existe.', $e);
        }
    }
}