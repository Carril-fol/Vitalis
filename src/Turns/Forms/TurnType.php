<?php
namespace App\Turns\Forms;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

use App\Patients\Models\Patient;
use App\Specialities\Models\Speciality;
use App\Users\Models\UserStatus;

class TurnType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('patient', EntityType::class, [
                'label' => 'Paciente',
                'class' => Patient::class,
                'placeholder' => 'Buscá por DNI o apellido',
                'autocomplete' => true,
                'choice_label' => fn (Patient $p) => sprintf(
                    '%s — %s, %s',
                    $p->getUser()->dni(),
                    $p->getUser()->lastName(),
                    $p->getUser()->firstName(),
                ),
                'query_builder' => fn (EntityRepository $repo) => $repo->createQueryBuilder('p')
                    ->join('p.user', 'u')
                    ->addSelect('u')
                    ->where('u.status = :active')
                    ->setParameter('active', UserStatus::Active)
                    ->orderBy('u.lastName'),
            ])
            ->add('speciality', EntityType::class, [
                'label' => 'Especialidad',
                'class' => Speciality::class,
                'placeholder' => 'Seleccione una especialidad',
                'autocomplete' => true,
                'choice_label' => 'name',
                'query_builder' => fn (EntityRepository $repo) => $repo->createQueryBuilder('s')->orderBy('s.name'),
            ])
            ->add('reason', TextType::class, [
                'label' => 'Motivo de consulta',
                'required' => false,
                'constraints' => [
                    new Assert\Length(max: 255, maxMessage: 'El motivo no puede superar los {{ limit }} caracteres'),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }
}
