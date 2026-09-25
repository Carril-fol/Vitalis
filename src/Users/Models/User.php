<?php
namespace App\Users\Models;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;

use App\Users\Models\UserStatus;
use App\Roles\Models\Role;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
#[UniqueEntity('dni', message: 'Ya hay un usuario con ese DNI')]
#[UniqueEntity('email', message: 'Ya hay un usuario con ese email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Role::class)]
    #[ORM\JoinColumn(name: 'role_id', nullable: false)]
    #[Assert\NotNull(message: 'Elegí un puesto')]
    private ?Role $role = null;

    #[ORM\Column(length: 8, unique: true)]
    #[Assert\NotBlank(message: 'El DNI es obligatorio')]
    #[Assert\Regex('/^\d{7,8}$/', message: 'El DNI tiene que ser de 7 u 8 dígitos, sin puntos ni espacios')]
    private string $dni = '';

    #[ORM\Column(name: 'first_name', length: 100)]
    #[Assert\NotBlank(message: 'El nombre es obligatorio')]
    #[Assert\Length(max: 100)]
    private string $firstName = '';

    #[ORM\Column(name: 'last_name', length: 100)]
    #[Assert\NotBlank(message: 'El apellido es obligatorio')]
    #[Assert\Length(max: 100)]
    private string $lastName = '';

    #[ORM\Column(length: 150, unique: true)]
    #[Assert\NotBlank(message: 'El email es obligatorio')]
    #[Assert\Email(message: '{{ value }} no es un email válido')]
    #[Assert\Length(max: 150)]
    private string $email = '';

    #[ORM\Column(name: 'password_hash', length: 255)]
    private string $passwordHash = '';

    #[ORM\Column(length: 30, nullable: true)]
    #[Assert\Length(max: 30)]
    private ?string $phone = null;

    #[ORM\Column(length: 150, nullable: true)]
    #[Assert\Length(max: 150)]
    private ?string $address = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\Length(max: 100)]
    private ?string $city = null;

    #[ORM\Column(length: 20, enumType: UserStatus::class)]
    private UserStatus $status = UserStatus::Active;

    #[ORM\Column(name: 'birth_date', type: 'date_immutable')]
    #[Assert\NotNull(message: 'La fecha de nacimiento es obligatoria')]
    #[Assert\LessThan('today', message: 'La fecha de nacimiento tiene que ser anterior a hoy')]
    private ?DateTimeImmutable $birthDate = null;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
    }

    public function deactivate(): void
    {
        $this->status = UserStatus::Inactive;
    }

    public function activate(): void
    {
        $this->status = UserStatus::Active;
    }

    public function isActive(): bool
    {
        return $this->status === UserStatus::Active;
    }

    public function initialPassword(): string
    {
        return mb_substr(mb_strtoupper(trim($this->firstName)), -3)
            . $this->dni
            . mb_substr(mb_strtoupper(trim($this->lastName)), -3);
    }

    public function fullName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function role(): ?Role
    {
        return $this->role;
    }

    public function dni(): string
    {
        return $this->dni;
    }

    public function firstName(): string
    {
        return $this->firstName;
    }

    public function lastName(): string
    {
        return $this->lastName;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function phone(): ?string
    {
        return $this->phone;
    }

    public function address(): ?string
    {
        return $this->address;
    }

    public function city(): ?string
    {
        return $this->city;
    }

    public function status(): UserStatus
    {
        return $this->status;
    }

    public function birthDate(): ?DateTimeImmutable
    {
        return $this->birthDate;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setRole(?Role $role): void
    {
        $this->role = $role;
    }

    public function setDni(?string $dni): void
    {
        $this->dni = (string) $dni;
    }

    public function setFirstName(?string $firstName): void
    {
        $this->firstName = mb_strtoupper((string) $firstName);
    }

    public function setLastName(?string $lastName): void
    {
        $this->lastName = mb_strtoupper((string) $lastName);
    }

    public function setEmail(?string $email): void
    {
        $this->email = mb_strtolower((string) $email);
    }

    public function setBirthDate(?DateTimeImmutable $birthDate): void
    {
        $this->birthDate = $birthDate;
    }

    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
    }

    public function setAddress(?string $address): void
    {
        $this->address = $address === null ? null : mb_strtoupper($address);
    }

    public function setCity(?string $city): void
    {
        $this->city = $city === null ? null : mb_strtoupper($city);
    }

    public function setPassword(string $passwordHash): void
    {
        $this->passwordHash = $passwordHash;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getRoles(): array
    {
        return array('ROLE_USER');
    }

    public function getPassword(): string
    {
        return $this->passwordHash;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
    }

    public function __serialize(): array
    {
        return array(
            'id'           => $this->id,
            'email'        => $this->email,
            'passwordHash' => hash('crc32c', $this->passwordHash),
        );
    }

    public function __unserialize(array $data): void
    {
        $this->id           = $data['id'];
        $this->email        = $data['email'];
        $this->passwordHash = $data['passwordHash'];
    }
}
