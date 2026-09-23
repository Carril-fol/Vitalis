<?php
namespace App\Users\Forms;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints as Assert;

use App\Users\Models\User;

class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, ['label' => 'Nombre'])
            ->add('lastName', TextType::class, ['label' => 'Apellido'])
            ->add('phone', TextType::class, ['label' => 'Teléfono', 'required' => false])
            ->add('address', TextType::class, ['label' => 'Dirección', 'required' => false])
            ->add('city', TextType::class, ['label' => 'Ciudad', 'required' => false])

            ->add('currentPassword', PasswordType::class, [
                'label' => 'Contraseña actual',
                'mapped' => false,
                'required' => false,
                'constraints' => [new UserPassword(
                    message: 'La contraseña actual no es correcta',
                    groups: ['password'],
                )],
            ])
            ->add('newPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'required' => false,
                'first_options' => ['label' => 'Contraseña nueva'],
                'second_options' => ['label' => 'Repetir la nueva'],
                'invalid_message' => 'Las dos contraseñas no coinciden',
                'constraints' => [new Assert\Length(
                    min: 6,
                    minMessage: 'La contraseña necesita al menos {{ limit }} caracteres',
                    groups: ['password'],
                )],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,

            'validation_groups' => static fn(FormInterface $form) => $form->get('newPassword')->getData()
                ? ['Default', 'password']
                : ['Default'],
        ]);
    }
}
