<?php
namespace App\MedicalStaff\Services;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use App\Core\NotFoundException;
use App\Core\ValidationException;

use App\MedicalStaff\Models\MedicalStaff;
use App\MedicalStaff\Repositories\MedicalStaffRepository;
use App\MedicalStaff\Interfaces\IMedicalStaffService;
use App\MedicalStaff\Schemas\MedicalStaffRegistrationSchema;
use App\MedicalStaff\Schemas\MedicalStaffSchema;
use App\MedicalStaff\Schemas\MedicalStaffUpdateSchema;

use App\Roles\Models\Role;
use App\Roles\Models\RoleArea;
use App\Roles\Interfaces\IRoleService;
use App\Specialities\Interfaces\ISpecialityService;

use App\Users\Schemas\UserSchema;
use App\Users\Interfaces\IUserService;


class MedicalStaffService implements IMedicalStaffService
{
    public function __construct(
        private readonly MedicalStaffRepository $repository,
        private readonly IRoleService $roleService,
        private readonly IUserService $userService,
        private readonly ISpecialityService $specialityService,
        private readonly ValidatorInterface $validator,
    ) {
    }

    /**
     * Private Methods
     */
    private function findOrFail(int $id): MedicalStaff
    {
        return $this->repository->find($id)
            ?? throw new NotFoundException('Personal médico no encontrado');
    }

    private function assertValid(MedicalStaffSchema $schema): void
    {
        $violations = $this->validator->validate($schema);

        if (count($violations) > 0) {
            throw ValidationException::fromViolations($violations);
        }
    }

    private function positionOrFail(int $roleId): Role
    {
        $role = $this->roleService->getRoleById($roleId);

        if (!$role || !in_array($roleId, array_map(fn($r) => $r->id(), $this->getPositions()))) {
            throw ValidationException::forField('roleId', 'Elegí un puesto de la lista');
        }

        return $role;
    }

    private function getSpecialityOrNull(?int $specialityId): ?object
    {
        return $specialityId !== null
            ? $this->specialityService->getSpecialityById($specialityId)
            : null;
    }

    /**
     * Public Methods
     */
    public function getPositions(): array
    {
        return $this->roleService->getRolesByArea(RoleArea::Medic);
    }

    public function getMedicalStaffById(int $id): MedicalStaff
    {
        return $this->findOrFail($id);
    }

    public function getAllMedicalStaff(): array
    {
        return $this->repository->findAll();
    }

    public function register(MedicalStaffRegistrationSchema $schema): int
    {
        $this->assertValid($schema);

        $user = $this->userService->registerUser(
            $schema->user,
            $this->positionOrFail($schema->roleId)
        );

        $medicalStaff = new MedicalStaff(
            $user,
            $this->getSpecialityOrNull($schema->specialityId),
            $schema->licenseNumber
        );

        $this->repository->save($medicalStaff, false);

        try {
            $this->repository->flush();
        } catch (UniqueConstraintViolationException $e) {
            throw ValidationException::forField('dni', 'Ya hay un usuario con ese DNI, email o matrícula', $e);
        }

        return $medicalStaff->id();
    }

    public function update(int $id, MedicalStaffUpdateSchema $schema): void
    {
        $medical = $this->findOrFail($id);
        $this->assertValid($schema);

        $this->userService->updateUser($medical->user(), $schema->user);
        $medical->user()->changeRole($this->positionOrFail($schema->roleId));
        $medical->setSpeciality($this->getSpecialityOrNull($schema->specialityId));
        $medical->setLicenseNumber($schema->licenseNumber);

        $this->repository->save($medical);
    }

    public function activate(int $id): void
    {
        $this->findOrFail($id)->user()->activate();
        $this->repository->flush();
    }

    public function deactivate(int $id): void
    {
        $this->findOrFail($id)->user()->deactivate();
        $this->repository->flush();
    }

    public function getMedicalStaffUpdateSchemaById(int $id): MedicalStaffUpdateSchema
    {
        $medical = $this->getMedicalStaffById($id);
        return MedicalStaffUpdateSchema::fromEntity($medical);
    }

    public function getMedicalStaffRegistrationSchema(): MedicalStaffRegistrationSchema
    {
        $schema = new MedicalStaffRegistrationSchema();
        $schema->user = new UserSchema();
        return $schema;
    }
}