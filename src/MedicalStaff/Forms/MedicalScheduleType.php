<?php
namespace App\MedicalStaff\Forms;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use App\ConsultingRooms\Models\ConsultingRoom;
use App\MedicalStaff\Models\MedicalSchedule;

class MedicalScheduleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('weekday', ChoiceType::class, [
                'label' => 'Día',
                'choices' => array_flip(MedicalSchedule::WEEKDAYS),
                'placeholder' => 'Elegí un día',
            ])
            ->add('startTime', TimeType::class, [
                'label' => 'Desde',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
            ])
            ->add('endTime', TimeType::class, [
                'label' => 'Hasta',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
            ])
            ->add('slotMinutes', ChoiceType::class, [
                'label' => 'Duración del turno',
                'choices' => ['10 min' => 10, '15 min' => 15, '20 min' => 20, '30 min' => 30, '45 min' => 45, '60 min' => 60],
            ])
            ->add('consultingRoom', EntityType::class, [
                'label' => 'Consultorio',
                'class' => ConsultingRoom::class,
                'choice_label' => 'name',
                'placeholder' => 'Elegí un consultorio',
                'query_builder' => fn(EntityRepository $repo) => $repo->createQueryBuilder('c')
                    ->where('c.active = true')
                    ->orderBy('c.name'),
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MedicalSchedule::class,
        ]);
    }
}
