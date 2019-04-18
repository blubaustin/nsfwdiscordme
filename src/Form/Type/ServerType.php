<?php
namespace App\Form\Type;

use App\Entity\Category;
use App\Entity\Server;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class ServerType
 */
class ServerType extends AbstractType
{
    /**
     * @var UrlGeneratorInterface
     */
    protected $urlGenerator;

    /**
     * Constructor
     *
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

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
                    'help' => 'This is the name of the server.'
                ]
            )
            ->add(
                'slug',
                null,
                [
                    'help' => 'Letters, numbers, and dashes are allowed.'
                ]
            )
            ->add(
                'discordID',
                TextType::class,
                [
                    'label' => 'Discord Server ID',
                    'help'  => 'This can be found in your server settings under "Widget".'
                ]
            )
            ->add(
                'summary',
                TextareaType::class,
                [
                    'help' => 'If your server is public this description will be shown.',
                    'attr' => [
                        'class' => 'form-control-description'
                    ]
                ]
            )
            ->add(
                'description',
                TextareaType::class,
                [
                    'required' => false,
                    'help'     => "Optional. If set, this will show on your server's individual page instead of the regular description. Markdown is supported with links and images disabled.",
                    'attr'     => [
                        'class' => 'form-control-description'
                    ]
                ]
            )
            ->add(
                'categories',
                EntityType::class,
                [
                    'class'        => Category::class,
                    'choice_label' => 'name',
                    'multiple'     => true
                ]
            )
            ->add(
                'botInviteChannelID',
                TextType::class,
                [
                    'required' => false,
                    'label'    => 'Bot Invite Channel ID',
                    'help'     => 'You will need to go into your Discord App Personal Settings -> Appearance -> Developer Mode to get the ID, and then you can disable Developer Mode after this step is complete. Now on Discord App <b>right click</b> the channel you want the invites to be created in and click "Copy ID". Paste the ID here. This must be set for bot invites to work. This would be your welcome/general channel.'
                ]
            )
            ->add(
                'botHumanCheck',
                null,
                [
                    'required'   => false,
                    'label'      => 'Require Bot Check',
                    'label_attr' => [
                        'class' => 'checkbox-custom'
                    ],
                    'help'       => 'This will help fight off those bots and raiders.'
                ]
            )
            ->add(
                'serverPassword',
                PasswordType::class,
                [
                    'required' => false,
                    'label'    => 'Server Join Password',
                    'attr'     => [
                        // Prevents the browser from auto completing the password field.
                        // Using the "autocomplete" attribute does not work in every browser.
                        'readonly' => 'readonly',
                        'onfocus'  => "javascript: this.removeAttribute('readonly')"
                    ]
                ]
            )
            ->add(
                'updatePassword',
                CheckboxType::class,
                [
                    'mapped'     => false,
                    'required'   => false,
                    'label'      => 'Updated password (Check and leave blank to remove password.)',
                    'label_attr' => [
                        'class' => 'checkbox-custom'
                    ],
                    'help'       => 'Ask the user for a password. This can be left blank.',
                ]
            )
            ->add(
                'isPublic',
                null,
                [
                    'required'   => false,
                    'label'      => 'Public',
                    'label_attr' => [
                        'class' => 'checkbox-custom'
                    ],
                    'help'       => 'This will add your server to our <a href="#">public server list!</a>'
                ]
            )
            ->add(
                'isActive',
                null,
                [
                    'required'   => false,
                    'label'      => 'Active',
                    'label_attr' => [
                        'class' => 'checkbox-custom'
                    ],
                    'help'       => 'If this is unchecked your Discord URL will not accept new people.'
                ]
            );
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => Server::class,
            ]
        );
    }
}
