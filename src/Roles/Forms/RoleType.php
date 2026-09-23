<?php
namespace App\Roles\Forms;

use Doctrine\ORM\EntityRepository;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use App\Permissions\Models\Permission;
use App\Roles\Models\Role;
use App\Roles\Models\RoleArea;


class RoleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nombre del rol',
            ])
            ->add('area', EnumType::class, [
                'label' => 'Área del rol',
                'class' => RoleArea::class,
                'choice_label' => fn(RoleArea $area) => $area->label(),
                'required' => false,
                'placeholder' => 'Sin área',
            ])
            ->add('permissions', EntityType::class, [
                'label' => 'Permisos',
                'class' => Permission::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'query_builder' => fn(EntityRepository $repo) => $repo->createQueryBuilder('p')->orderBy('p.name'),
                'group_by' => fn(Permission $permission) => explode('_', $permission->getName(), 2)[1] ?? 'Otros',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Role::class,
        ]);
    }
}