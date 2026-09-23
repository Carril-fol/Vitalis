<?php
namespace App\Administratives\Forms;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use App\Administratives\Models\Administrative;
use App\Roles\Models\RoleArea;
use App\Users\Forms\UserType;

class AdministrativeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('user', UserType::class, [
                'label' => false,
                'role_area' => RoleArea::Administrative,
                'require_password' => $options['require_password'],
            ])
            ->add('sector', TextType::class, [
                'label' => 'Sector',
            ])
            ->add('notes', TextareaType::class, [
                'label' => 'Notas',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Administrative::class,
            'require_password' => true,
        ]);
        $resolver->setAllowedTypes('require_password', 'bool');
    }
}
