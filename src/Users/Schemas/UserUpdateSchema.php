<?php
namespace App\Users\Schemas;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use App\Users\Models\User;

class UserUpdateSchema
{
    #[Assert\NotBlank(message: 'El nombre es obligatorio')]
    #[Assert\Length(max: 100, maxMessage: 'El nombre no puede superar los {{ limit }} caracteres')]
    public string $firstName = '';

    #[Assert\NotBlank(message: 'El apellido es obligatorio')]
    #[Assert\Length(max: 100, maxMessage: 'El apellido no puede superar los {{ limit }} caracteres')]
    public string $lastName = '';

    #[Assert\NotBlank(message: 'El DNI es obligatorio')]
    #[Assert\Regex(
        pattern: '/^\d{7,8}$/',
        message: 'El DNI tiene que ser de 7 u 8 digitos, sin puntos ni espacios'
    )]
    public string $dni = '';

    #[Assert\NotBlank(message: 'El email es obligatorio')]
    #[Assert\Email(message: '{{ value }} no es un email valido')]
    #[Assert\Length(max: 150, maxMessage: 'El email no puede superar los {{ limit }} caracteres')]
    public string $email = '';

    #[Assert\Length(min: 6, minMessage: 'La contrasena necesita al menos {{ limit }} caracteres')]
    public ?string $password = null;

    #[Assert\NotBlank(message: 'La fecha de nacimiento es obligatoria')]
    #[Assert\Date(message: 'La fecha de nacimiento no es una fecha valida')]
    public string $birthDate = '';

    #[Assert\Length(max: 30, maxMessage: 'El telefono no puede superar los {{ limit }} caracteres')]
    public ?string $phone = null;

    #[Assert\Length(max: 150, maxMessage: 'La direccion no puede superar los {{ limit }} caracteres')]
    public ?string $address = null;

    #[Assert\Length(max: 100, maxMessage: 'La ciudad no puede superar los {{ limit }} caracteres')]
    public ?string $city = null;

    #[Assert\Callback]
    public function validateBirthDate(ExecutionContextInterface $context): void
    {
        if ($this->birthDate === '') {
            return;
        }

        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $this->birthDate);

        if ($date === false) {
            return;
        }

        if ($date > new \DateTimeImmutable('today')) {
            $context->buildViolation('La fecha de nacimiento tiene que ser anterior a hoy')
                ->atPath('birthDate')
                ->addViolation();
        }
    }

    public static function fromPost(array $post): self
    {
        $schema = new self();

        $schema->firstName = self::required($post['first_name'] ?? null);
        $schema->lastName  = self::required($post['last_name'] ?? null);
        $schema->dni       = self::required($post['dni'] ?? null);
        $schema->email     = self::required($post['email'] ?? null);
        $schema->birthDate = self::required($post['birth_date'] ?? null);

        $schema->password  = self::optional($post['password'] ?? null);
        $schema->phone      = self::optional($post['phone'] ?? null);
        $schema->address   = self::optional($post['address'] ?? null);
        $schema->city       = self::optional($post['city'] ?? null);

        return $schema;
    }

    public static function fromEntity(User $user): self
    {
        $schema = new self();
        $schema->firstName = $user->firstName();
        $schema->lastName  = $user->lastName();
        $schema->dni       = $user->dni();
        $schema->email     = $user->email();
        $schema->password   = null;
        $schema->birthDate = $user->birthDate()->format('Y-m-d');
        $schema->phone      = $user->phone();
        $schema->address    = $user->address();
        $schema->city       = $user->city();

        return $schema;
    }

    private static function required(?string $value): string
    {
        return trim($value ?? '');
    }

    private static function optional(?string $value): ?string
    {
        $value = trim($value ?? '');

        return $value === '' ? null : $value;
    }
}