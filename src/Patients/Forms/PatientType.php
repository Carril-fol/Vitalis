<?php
namespace App\Patients\Forms;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use App\HealthInsurances\Models\HealthInsurance;
use App\Roles\Models\RoleArea;
use App\Patients\Models\Patient;
use App\Users\Forms\UserType;


class PatientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('user', UserType::class, [
                'label' => false,
                'role_area' => RoleArea::Patient,
                'require_password' => $options['require_password'],
            ])
            ->add('healthInsurance', EntityType::class, [
                'label' => 'Obra social',
                'class' => HealthInsurance::class,
                'choice_label' => 'name',
                'placeholder' => 'Seleccione una obra social',
                'query_builder' => fn(EntityRepository $repo) => $repo->createQueryBuilder('hi')->orderBy('hi.name'),
            ])
            ->add('memberNumber', TextType::class, [
                'label' => 'Número de socio',
            ]);
        $builder->get('user')->remove('role');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Patient::class,
            'require_password' => true,
        ]);
        $resolver->setAllowedTypes('require_password', 'bool');
    }
}