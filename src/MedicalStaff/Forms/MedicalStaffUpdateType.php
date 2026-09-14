<?php
namespace App\MedicalStaff\Forms;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\MedicalStaff\Schemas\MedicalStaffUpdateSchema;

class MedicalStaffUpdateType extends MedicalStaffRegistrationType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);
        $builder->add('user', UserUpdateType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'data_class' => MedicalStaffUpdateSchema::class,
        ]);
    }
}
