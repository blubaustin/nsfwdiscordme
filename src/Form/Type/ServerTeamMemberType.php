<?php
namespace App\Form\Type;

use App\Entity\ServerTeamMember;
use App\Form\Model\ServerTeamMemberModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ServerTeamMemberType
 */
class ServerTeamMemberType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'username',
                TextType::class,
                [
                    'label' => 'Discord User',
                    'required' => true,
                    'attr' => [
                        'placeholder' => 'Discord username#discriminator or ID'
                    ]
                ]
            )
            ->add(
                'role',
                ChoiceType::class,
                [
                    'label' => 'Role',
                    'required' => true,
                    'empty_data' => '',
                    'choices'  => [
                        'Select...' => '',
                        'Manager'   => 'manager',
                        'Editor'    => 'editor'
                    ]
                ]
            )
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => ServerTeamMemberModel::class,
            ]
        );
    }
}
