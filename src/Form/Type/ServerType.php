<?php
namespace App\Form\Type;

use App\Discord\Discord;
use App\Entity\Category;
use App\Entity\Server;
use App\Entity\User;
use App\Repository\TagRepository;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
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
     * @var Discord
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
     * @param Discord               $discord
     * @param UrlGeneratorInterface $urlGenerator
     * @param TagRepository         $tagRepository
     */
    public function __construct(Discord $discord, UrlGeneratorInterface $urlGenerator, TagRepository $tagRepository)
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
                'tags',
                TextType::class,
                [
                    'required' => false,
                    'label' => 'Tags',
                    'help' => 'Comma separated list of tags describing the server.'
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
                'iconFile',
                FileType::class,
                [
                    'mapped' => false,
                    'required' => false,
                    'label'    => 'Icon Image'
                ]
            )
            ->add(
                'bannerFile',
                FileType::class,
                [
                    'mapped' => false,
                    'required' => false,
                    'label'    => 'Banner Image'
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
            )
        ;

        $builder->get('tags')
            ->addModelTransformer(new CallbackTransformer(
                function ($tagsCollection) {
                    $tags = [];
                    foreach($tagsCollection as $tag) {
                        $tags[] = $tag->getName();
                    }
                    return implode(', ', $tags);
                },
                function ($tagsAsString) {
                    $tags = array_filter(array_map('trim', explode(',', $tagsAsString)));
                    return $this->tagRepository->stringsToTags($tags);
                }
            ))
        ;

        if ($options['user']) {
            $user        = $options['user'];
            $accessToken = $user->getDiscordAccessToken();

            $servers = [
                'Select...' => 0
            ];
            foreach($this->discord->fetchMeGuilds($accessToken) as $server) {
                $servers[$server['name']] = $server['id'];
            }

            $builder->add(
                'discordID',
                ChoiceType::class,
                [
                    'label' => 'Discord Server',
                    'help'  => 'Which one of your servers are you adding?',
                    'choices' => $servers
                ]
            );
        } else {
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
                'user'       => null
            ]
        );
        $resolver->setAllowedTypes('user', [User::class]);
    }
}
