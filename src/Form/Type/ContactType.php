<?php
namespace App\Form\Type;

use App\Form\Model\ContactModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ContactType
 */
class ContactType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name',
                null,
                [
                    'help' => 'Your full name.',
                    'required' => true
                ]
            )
            ->add(
                'email',
                null,
                [
                    'help' => 'Your email address.',
                    'required' => true
                ]
            )
            ->add(
                'subject',
                null,
                [
                    'help' => 'The subject of your message.',
                    'required' => true
                ]
            )
            ->add(
                'message',
                TextareaType::class,
                [
                    'help' => 'What would you like to say?',
                    'required' => true,
                    'attr' => [
                        'class' => 'form-control-description'
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
                'data_class' => ContactModel::class,
            ]
        );
    }
}
