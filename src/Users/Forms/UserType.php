<?php
namespace App\Users\Forms;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

use App\Roles\Models\Role;
use App\Roles\Models\RoleArea;
use App\Users\Models\User;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, ['label' => 'Nombre'])
            ->add('lastName', TextType::class, ['label' => 'Apellido'])
            ->add('dni', TextType::class, ['label' => 'DNI'])
            ->add('email', EmailType::class, ['label' => 'Email'])
            ->add('birthDate', DateType::class, [
                'label' => 'Fecha de nacimiento',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'invalid_message' => 'La fecha de nacimiento no es una fecha válida',
            ])
            ->add('phone', TextType::class, ['label' => 'Teléfono', 'required' => false])
            ->add('address', TextType::class, ['label' => 'Dirección', 'required' => false])
            ->add('city', TextType::class, ['label' => 'Ciudad', 'required' => false])
            ->add('role', EntityType::class, [
                'label' => 'Puesto',
                'class' => Role::class,
                'choice_label' => 'name',
                'placeholder' => 'Seleccione un puesto',
                'query_builder' => fn(EntityRepository $repo) => $repo->createQueryBuilder('r')
                    ->where('r.area = :area')
                    ->setParameter('area', $options['role_area']->value)
                    ->orderBy('r.name'),
            ]);

        if ($options['with_password']) {
            $builder->add('plainPassword', PasswordType::class, [
                'label' => 'Contraseña',
                'mapped' => false,
                'required' => false,
                'constraints' => [new Assert\Length(min: 6, minMessage: 'La contraseña necesita al menos {{ limit }} caracteres')],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'with_password' => false,
        ]);
        $resolver->setRequired('role_area');
        $resolver->setAllowedTypes('role_area', RoleArea::class);
        $resolver->setAllowedTypes('with_password', 'bool');
    }
}