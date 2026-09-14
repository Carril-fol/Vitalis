<?php
namespace App\MedicalStaff\Forms;

use App\Users\Schemas\UserUpdateSchema;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserUpdateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Nombre',
                'required' => false,
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Apellido',
                'required' => false,
            ])
            ->add('dni', TextType::class, [
                'label' => 'DNI',
                'required' => false,

            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'required' => false,
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Contraseña',
                'required' => false,
            ])
            ->add('birthDate', TextType::class, [
                'label' => 'Fecha de Nacimiento',
                'attr' => ['placeholder' => 'AAAA-MM-DD'],
            ])
            ->add('phone', TextType::class, [
                'label' => 'Teléfono',
                'required' => false,
            ])
            ->add('address', TextType::class, [
                'label' => 'Dirección',
                'required' => false,
            ])
            ->add('city', TextType::class, [
                'label' => 'Ciudad',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserUpdateSchema::class,
        ]);
    }
}
