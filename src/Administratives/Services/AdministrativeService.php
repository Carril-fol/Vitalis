<?php
namespace App\Administratives\Services;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use App\Core\NotFoundException;
use App\Core\ValidationException;

use App\Users\Interfaces\IUserService;
use App\Roles\Interfaces\IRoleService;
use App\Roles\Models\Role;
use App\Roles\Models\RoleArea;

use App\Administratives\Models\Administrative;
use App\Administratives\Interfaces\IAdministrativeService;
use App\Administratives\Repositories\AdministrativeRepository;
use App\Administratives\Schemas\AdministrativeSchema;
use App\Administratives\Schemas\AdministrativeRegistrationSchema;


class AdministrativeService implements IAdministrativeService
{
    public function __construct(
        private readonly AdministrativeRepository $repository,
        private readonly IUserService $userService,
        private readonly IRoleService $roleService,
        private readonly ValidatorInterface $validator,
    ) {
    }

    /**
     * Private Methods
     * ==========================================
     */

    private function changeStatus(int $id, bool $active): void
    {
        $user = $this->findOrFail($id)->user();
        $active ? $user->activate() : $user->deactivate();

        $this->repository->flush();
    }

    private function positionOrFail(int $roleId): Role
    {
        foreach ($this->getPositions() as $role) {
            if ($role->id() === $roleId) {
                return $role;
            }
        }

        throw ValidationException::forField('roleId', 'Elegí un puesto de la lista');
    }

    private function findOrFail(int $id): Administrative
    {
        return $this->repository->find($id)
            ?? throw new NotFoundException('Administrative not found');
    }

    private function assertValid(AdministrativeSchema $schema): void
    {
        $violations = $this->validator->validate($schema);

        if (count($violations) > 0) {
            throw ValidationException::fromViolations($violations);
        }
    }

    /**
     * Public Methods
     * ==========================================
     */
    public function getPositions(): array
    {
        return $this->roleService->getRolesByArea(RoleArea::Administrative);
    }

    public function getAdministrativeById(int $id): array
    {
        return $this->findOrFail($id)->toArray();
    }

    public function findByUserId(int $userId): ?array
    {
        return $this->repository->findByUser($userId)?->toArray();
    }

    public function findByName(string $name): array
    {
        return $this->repository->findByName($name);
    }

    public function findAll(): array
    {
        return $this->repository->findAllWithUser();
    }

    public function register(AdministrativeRegistrationSchema $schema): int
    {
        $this->assertValid($schema);

        $role = $this->positionOrFail($schema->roleId);
        $user = $this->userService->registerUser($schema->user, $role);

        $administrative = new Administrative($user, $schema->sector, $schema->notes);
        $this->repository->save($administrative, false);

        try {
            $this->repository->flush();
        } catch (UniqueConstraintViolationException $e) {
            throw ValidationException::forField('dni', 'Ya hay un usuario con ese DNI o ese email', $e);
        }

        return $administrative->id();
    }

    public function update(int $id, AdministrativeSchema $schema): void
    {
        $administrative = $this->findOrFail($id);
        $this->assertValid($schema);

        $administrative->user()->changeRole($this->positionOrFail($schema->roleId));
        $administrative->setSector($schema->sector);
        $administrative->setNotes($schema->notes);

        $this->repository->save($administrative);
    }

    public function activate(int $id): void
    {
        $this->changeStatus($id, true);
    }

    public function deactivate(int $id): void
    {
        $this->changeStatus($id, false);
    }
}