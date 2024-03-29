<?php
namespace App\Form\Type;

use App\Entity\Category;
use App\Entity\Server;
use App\Entity\User;
use App\Repository\TagRepository;
use App\Services\DiscordService;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
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
     * @var DiscordService
     */
    protected $discord;

    /**
     * @var UrlGeneratorInterface
     */
    protected $urlGenerator;

    /**
     * @var TagRepository
     */
    protected $tagRepository;

    /**
     * Constructor
     *
     * @param DiscordService        $discord
     * @param UrlGeneratorInterface $urlGenerator
     * @param TagRepository         $tagRepository
     */
    public function __construct(DiscordService $discord, UrlGeneratorInterface $urlGenerator, TagRepository $tagRepository)
    {
        $this->discord       = $discord;
        $this->urlGenerator  = $urlGenerator;
        $this->tagRepository = $tagRepository;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     *
     * @throws Exception
     * @throws GuzzleException
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
                    'help'     => "This will show on your server's individual page. Markdown is supported with links and images disabled.",
                    'attr'     => [
                        'class' => 'form-control-description'
                    ]
                ]
            )
            ->add(
                'category1',
                EntityType::class,
                [
                    'class'        => Category::class,
                    'label'        => 'First Category',
                    'choice_label' => 'name',
                    'placeholder'  => 'Select...',
                    'empty_data'   => ''
                ]
            )
            ->add(
                'category2',
                EntityType::class,
                [
                    'class'        => Category::class,
                    'label'        => 'Second Category',
                    'choice_label' => 'name',
                    'placeholder'  => 'Select...',
                    'empty_data'   => ''
                ]
            )
            ->add(
                'tags',
                TextType::class,
                [
                    'required' => false,
                    'label'    => 'Tags',
                    'help'     => 'Comma separated list of tags describing the server.'
                ]
            )
            ->add(
                'inviteType',
                ChoiceType::class,
                [
                    'choices'    => [
                        'Select...'                   => '',
                        'Widget with Instant Invite'  => 'widget',
                        'Our Bot Creates the Invites' => 'bot'
                    ],
                    'empty_data' => ''
                ]
            )
            ->add(
                'botInviteChannelID',
                TextType::class,
                [
                    'required' => false,
                    'label'    => 'Bot Invite Channel'
                ]
            )
            ->add(
                'bannerFile',
                FileType::class,
                [
                    'mapped'   => false,
                    'required' => false,
                    'label'    => 'Banner Image'
                ]
            )
            ->add(
                'bannerCropData',
                HiddenType::class,
                [
                    'mapped' => false
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
                    'required'   => false,
                    'empty_data' => '',
                    'label'      => 'Server Join Password',
                    'attr'       => [
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

        $builder->get('tags')
            ->addModelTransformer(
                new CallbackTransformer(
                    function ($tagsCollection) {
                        $tags = [];
                        foreach ($tagsCollection as $tag) {
                            $tags[] = $tag->getName();
                        }

                        return implode(', ', $tags);
                    },
                    function ($tagsAsString) {
                        $tags = array_filter(array_map('trim', explode(',', $tagsAsString)));

                        return $this->tagRepository->stringsToTags($tags);
                    }
                )
            );

        if ($options['isEditing']) {
            $builder->add('discordID');
        } else if ($options['user'] && ($accessToken = $options['user']->getDiscordAccessToken())) {
            $servers = [
                'Select...' => ''
            ];
            foreach ($this->discord->fetchMeGuilds($accessToken) as $server) {
                $servers[$server['name']] = $server['id'];
            }

            $builder->add(
                'discordID',
                ChoiceType::class,
                [
                    'label'   => 'Discord Server',
                    'help'    => 'Which one of your servers are you adding?',
                    'choices' => $servers
                ]
            );
        } else {
            // Otherwise make it a plain text field. This will be used by the admin
            // site to make the discord ID editable.
            $builder->add('discordID');
        }
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => Server::class,
                'user'       => null,
                'isEditing'  => false
            ]
        );
        $resolver->setAllowedTypes('user', [User::class]);
    }
}
