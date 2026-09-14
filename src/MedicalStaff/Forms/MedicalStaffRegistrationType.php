<?php
namespace App\MedicalStaff\Forms;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use App\MedicalStaff\Schemas\MedicalStaffRegistrationSchema;
use App\MedicalStaff\Forms\UserType;


class MedicalStaffRegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $positionChoices = [];
        foreach ($options['positions'] as $role) {
            $positionChoices[$role->name()] = $role->id();
        }

        $specialityChoices = [];
        foreach ($options['specialities'] as $speciality) {
            $specialityChoices[$speciality['name']] = $speciality['id'];
        }

        $builder
            ->add('user', UserType::class)

            ->add('roleId', ChoiceType::class, [
                'choices' => $positionChoices,
                'placeholder' => 'Seleccione un puesto',
            ])
            ->add('specialityId', ChoiceType::class, [
                'choices' => $specialityChoices,
                'required' => false,
                'placeholder' => 'Sin especialidad',
            ])
            ->add('hasLicense', CheckboxType::class, [
                'required' => false,
            ])
            ->add('licenseNumber', TextType::class, [
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MedicalStaffRegistrationSchema::class,
            'positions' => [],
            'specialities' => [],
        ]);
    }
}
