<?php
namespace App\Users\Models;

use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;

use App\Users\Models\UserStatus;
use App\Roles\Models\Role;


#[ORM\Entity]
#[ORM\Table(name: 'users')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Role::class)]
    #[ORM\JoinColumn(name: 'role_id', nullable: false)]
    private Role $role;

    #[ORM\Column(length: 8, unique: true)]
    private string $dni;

    #[ORM\Column(name: 'first_name', length: 100)]
    private string $firstName;

    #[ORM\Column(name: 'last_name', length: 100)]
    private string $lastName;

    #[ORM\Column(length: 150, unique: true)]
    private string $email;

    #[ORM\Column(name: 'password_hash', length: 255)]
    private string $passwordHash;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $address = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $city = null;

    #[ORM\Column(length: 20, enumType: UserStatus::class)]
    private UserStatus $status = UserStatus::Active;

    #[ORM\Column(name: 'birth_date', type: 'date_immutable')]
    private DateTimeImmutable $birthDate;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    private function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
    }

    public static function register(
        Role $role,
        string $dni,
        string $firstName,
        string $lastName,
        string $email,
        string $plainPassword,
        DateTimeImmutable $birthDate,
    ): self {
        $user = new self();

        $user->role = $role;
        $user->dni = trim($dni);
        $user->firstName = mb_strtoupper(trim($firstName));
        $user->lastName = mb_strtoupper(trim($lastName));
        $user->email = mb_strtolower(trim($email));
        $user->passwordHash = password_hash($plainPassword, PASSWORD_DEFAULT);
        $user->birthDate = $birthDate;

        return $user;
    }

    public function changePassword(string $plainPassword): void
    {
        $this->passwordHash = password_hash($plainPassword, PASSWORD_DEFAULT);
    }

    public function withContactData(?string $phone, ?string $address, ?string $city): self
    {
        $this->phone = $phone;
        $this->address = $address === null ? null : mb_strtoupper($address);
        $this->city = $city === null ? null : mb_strtoupper($city);

        return $this;
    }

    public function changeRole(Role $role): void
    {
        $this->role = $role;
    }

    public function updateInfo(
        string $dni,
        string $firstName,
        string $lastName,
        string $email,
        DateTimeImmutable $birthDate,
    ): void {
        $this->dni = trim($dni);
        $this->firstName = mb_strtoupper(trim($firstName));
        $this->lastName = mb_strtoupper(trim($lastName));
        $this->email = mb_strtolower(trim($email));
        $this->birthDate = $birthDate;
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

    public function fullName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function role(): Role
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

    public function passwordHash(): string
    {
        return $this->passwordHash;
    }

    public function status(): UserStatus
    {
        return $this->status;
    }

    public function birthDate(): DateTimeImmutable
    {
        return $this->birthDate;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    // --- Security ---

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    /**
     * Todos los usuarios logueados tienen ROLE_USER. Lo que cada uno puede
     * hacer lo decide el voter de permisos segun su Role, no esta lista.
     */
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
