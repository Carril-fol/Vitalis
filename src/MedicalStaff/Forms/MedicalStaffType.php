<?php
namespace App\MedicalStaff\Forms;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use App\MedicalStaff\Models\MedicalStaff;
use App\Roles\Models\RoleArea;
use App\Specialities\Models\Speciality;
use App\Users\Forms\UserType;

class MedicalStaffType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('user', UserType::class, [
                'label' => false,
                'role_area' => RoleArea::Medic,
                'with_password' => $options['with_password'],
            ])
            ->add('speciality', EntityType::class, [
                'label' => 'Especialidad',
                'class' => Speciality::class,
                'choice_label' => 'name',
                'required' => false,
                'placeholder' => 'Sin especialidad',
            ])
            ->add('licenseNumber', TextType::class, [
                'label' => 'Número de matrícula',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MedicalStaff::class,
            'with_password' => false,
        ]);
        $resolver->setAllowedTypes('with_password', 'bool');
    }
}
