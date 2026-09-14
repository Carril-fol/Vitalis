<?php
namespace App\Roles\Schemas;

use Symfony\Component\Validator\Constraints as Assert;

use App\Roles\Models\RoleArea;


class CreateRole
    {
        /**
         * @var string
        */
        #[Assert\NotBlank(message: 'El nombre es obligatorio')]
        #[Assert\Length(max: 50, maxMessage: 'El nombre no puede superar los {{ limit }} caracteres')]
        public string $name = '';

        /**
         * @var string
        */
        #[Assert\Choice(callback: 'areaChoices', message: 'El área no es válida')]
        public string $area = '';

        /**
         * @var int[]
         */
        #[Assert\All(array(new Assert\Positive(message: 'Hay un permiso que no es válido'),))]
        public array $permissionIds = array();

        /**
         * Summary of fromPost
         * @param array $post
         * @return CreateRole
         */
        public static function fromPost(array $post): static
        {
            $schema = new static();

            $schema->name = trim($post['name'] ?? '');
            $schema->area = trim((string) ($post['area'] ?? ''));

            $ids = $post['permissions'] ?? array();
            if (!is_array($ids)) {
                $ids = array();
            }

            $schema->permissionIds = array_values(array_unique(array_map(
                fn($id) => ctype_digit((string) $id) ? (int) $id : 0,
                $ids
            )));
            return $schema;
        }

        /**
         * Summary of areaValue
         * @return RoleArea|null
         */
        public function areaValue(): ?RoleArea
        {
            return $this->area === '' ? null : RoleArea::from($this->area);
        }

        /** @return string[] */
        public static function areaChoices(): array
        {
            return array_merge(array(''), array_map(fn(RoleArea $area) => $area->value, RoleArea::cases()));
        }
    }
