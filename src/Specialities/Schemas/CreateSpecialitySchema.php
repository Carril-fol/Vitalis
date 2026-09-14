<?php
namespace App\Specialities\Schemas;

use Symfony\Component\Validator\Constraints as Assert;

class CreateSpecialitySchema
{
    #[Assert\NotBlank(message: 'El nombre es obligatorio')]
    #[Assert\Length(max: 50, maxMessage: 'El nombre no puede superar los {{ limit }} caracteres')]
    public string $name = '';

    // static y no self: asi UpdateSpecialitySchema::fromPost() devuelve un
    // UpdateSpecialitySchema, que es lo que pide updateSpeciality().
    public static function fromPost(array $post): static
    {
        $schema = new static();
        $schema->name = trim($post['name'] ?? '');
        return $schema;
    }
}