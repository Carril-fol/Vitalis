<?php
namespace App\Users\Schemas;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class UserSchema
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

    #[Assert\NotBlank(message: 'La contrasena es obligatoria')]
    #[Assert\Length(min: 6, minMessage: 'La contrasena necesita al menos {{ limit }} caracteres')]
    public string $password = '';

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
}