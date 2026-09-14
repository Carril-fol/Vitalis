<?php
namespace App\Users\Services;

use DateTimeImmutable;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use App\Core\ValidationException;

use App\Users\Models\User;
use App\Users\Schemas\UserSchema;
use App\Users\Schemas\UserUpdateSchema;
use App\Users\Interfaces\IUserService;
use App\Users\Repositories\UserRepository;

use App\Roles\Interfaces\IRoleService;
use App\Roles\Models\Role;


class UserService implements IUserService {
    private UserRepository $repository;
    private ValidatorInterface $validator;

    function __construct(
        UserRepository $repository,
        ValidatorInterface $validator
    ) {
        $this->repository = $repository;
        $this->validator = $validator;
    }

    public function getUserByRole(Role $role): array
    {
        return $this->repository->findByRole($role);
    }

    public function registerUser(UserSchema $schema, Role $role): User
    {
        $violations = $this->validator->validate($schema);

        if (count($violations) > 0) {
            throw ValidationException::fromViolations($violations);
        }

        if ($this->repository->findByEmail($schema->email)) {
            throw ValidationException::forField('email', 'Ya hay un usuario con ese email');
        }

        if ($this->repository->findByDni($schema->dni)) {
            throw ValidationException::forField('dni', 'Ya hay un usuario con ese DNI');
        }

        $user = User::register(
            $role,
            $schema->dni,
            $schema->firstName,
            $schema->lastName,
            $schema->email,
            $schema->password,
            new DateTimeImmutable($schema->birthDate),
        )->withContactData($schema->phone, $schema->address, $schema->city);

        $this->repository->save($user, false);

        return $user;
    }

    public function updateUser(User $user, UserUpdateSchema $schema): void
    {
        $violations = $this->validator->validate($schema);

        if (count($violations) > 0) {
            throw ValidationException::fromViolations($violations);
        }

        // Evitar que el usuario cambie su email o DNI por uno que ya existe en otro usuario
        $existingEmail = $this->repository->findByEmail($schema->email);
        if ($existingEmail && $existingEmail->id() !== $user->id()) {
            throw ValidationException::forField('email', 'Ya hay un usuario con ese email');
        }

        $existingDni = $this->repository->findByDni($schema->dni);
        if ($existingDni && $existingDni->id() !== $user->id()) {
            throw ValidationException::forField('dni', 'Ya hay un usuario con ese DNI');
        }

        $user->updateInfo(
            $schema->dni,
            $schema->firstName,
            $schema->lastName,
            $schema->email,
            new DateTimeImmutable($schema->birthDate),
        );

        $user->withContactData($schema->phone, $schema->address, $schema->city);

        if ($schema->password !== null) {
            $user->changePassword($schema->password);
        }

        $this->repository->save($user);
    }
}