<?php
namespace App\Controller;

use App\Entity\BannedServer;
use App\Entity\BannedWord;
use App\Entity\BumpPeriodVote;
use App\Entity\ServerJoinEvent;
use App\Entity\Media;
use App\Entity\Server;
use App\Entity\ServerFollow;
use App\Entity\ServerTeamMember;
use App\Entity\ServerViewEvent;
use App\Entity\User;
use App\Event\ServerActionEvent;
use App\Event\ViewEvent;
use App\Form\Model\ServerTeamMemberModel;
use App\Form\Type\ServerTeamMemberType;
use App\Http\Request;
use App\Form\Type\ServerType;
use App\Media\Adapter\Exception\FileExistsException;
use App\Media\Adapter\Exception\FileNotFoundException;
use App\Media\Adapter\Exception\WriteException;
use App\Media\Paths;
use App\Media\WebHandlerInterface;
use DateTime;
use Exception;
use Gumlet\ImageResize;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(name="server_")
 */
class ServerController extends Controller
{
    /**
     * @var WebHandlerInterface
     */
    protected $webHandler;

    /**
     * @param WebHandlerInterface $webHandler
     */
    public function setWebHandler(WebHandlerInterface $webHandler)
    {
        $this->webHandler = $webHandler;
    }

    /**
     * @Route("/{slug}", name="index")
     *
     * @param string  $slug
     *
     * @param Request $request
     *
     * @return Response
     * @throws Exception
     */
    public function indexAction($slug, Request $request)
    {
        $server      = $this->fetchServerOrThrow($slug);
        $user        = $this->getUser();
        $isFollowing = false;
        if ($user) {
            $isFollowing = $this->em->getRepository(ServerFollow::class)->isFollowing($server, $user);
        }

        $this->eventDispatcher->dispatch('app.server.view', new ViewEvent($server, $request));

        return $this->render('server/index.html.twig', [
            'server'      => $server,
            'isFollowing' => $isFollowing
        ]);
    }

    /**
     * @Route("/server/follow/{slug}", name="follow")
     *
     * @param string $slug
     *
     * @return Response
     */
    public function followAction($slug)
    {
        $user = $this->getUser();
        if (!$user) {
            return new RedirectResponse($this->generateUrl('discord_oauth2'));
        }

        $server = $this->fetchServerOrThrow($slug);

        return $this->render('server/follow.html.twig', [
            'server'      => $server,
            'isFollowing' => $this->em->getRepository(ServerFollow::class)->isFollowing($server, $user)
        ]);
    }

    /**
     * @Route("/server/follow-confirm/{slug}", name="follow_confirm", methods={"POST"})
     *
     * @param string $slug
     *
     * @return Response
     * @throws Exception
     */
    public function followConfirmAction($slug)
    {
        $user = $this->getUser();
        if (!$user) {
            return new RedirectResponse($this->generateUrl('discord_oauth2'));
        }

        $server = $this->fetchServerOrThrow($slug);
        $follow = $this->em->getRepository(ServerFollow::class)->findFollow($server, $user);

        if ($follow) {
            $this->em->remove($follow);
            $this->addFlash('success', 'You are no longer following the server.');
        } else {
            $follow = (new ServerFollow())
                ->setServer($server)
                ->setUser($user);
            $this->em->persist($follow);
            $this->addFlash('success', 'You are now following the server.');
        }

        $this->em->flush();

        return new RedirectResponse($this->generateUrl('server_index', ['slug' => $server->getSlug()]));
    }

    /**
     * @Route("/server/stats/{slug}", name="stats")
     *
     * @param string $slug
     *
     * @return Response
     * @throws Exception
     */
    public function statsAction($slug)
    {
        $server = $this->fetchServerOrThrow($slug);
        if (!$this->hasServerAccess($server, self::SERVER_ROLE_EDITOR)) {
            throw $this->createAccessDeniedException();
        }

        $bumpLog = $this->em->getRepository(BumpPeriodVote::class)
            ->createQueryBuilder('b')
            ->where('b.server = :server')
            ->andWhere('b.dateCreated >= :date')
            ->setParameter(':server', $server)
            ->setParameter(':date', new DateTime('10 days ago'))
            ->orderBy('b.id', 'desc')
            ->getQuery()
            ->execute();

        $joinCount = $this->em->getRepository(ServerJoinEvent::class)
            ->createQueryBuilder('j')
            ->select('COUNT(j.id)')
            ->where('j.server = :server')
            ->setParameter(':server', $server)
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleScalarResult();

        $viewCount = $this->em->getRepository(ServerViewEvent::class)
            ->createQueryBuilder('j')
            ->select('COUNT(j.id)')
            ->where('j.server = :server')
            ->setParameter(':server', $server)
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleScalarResult();

        return $this->render('server/stats.html.twig', [
            'server'    => $server,
            'bumpLog'   => $bumpLog,
            'joinCount' => $joinCount,
            'viewCount' => $viewCount
        ]);
    }

    /**
     * @Route("/server/team/{slug}", name="team")
     *
     * @param string  $slug
     *
     * @param Request $request
     *
     * @return Response
     * @throws GuzzleException
     */
    public function teamAction($slug, Request $request)
    {
        $server = $this->fetchServerOrThrow($slug);
        if (!$this->hasServerAccess($server, self::SERVER_ROLE_MANAGER)) {
            throw $this->createAccessDeniedException();
        }

        $model = new ServerTeamMemberModel();
        $form  = $this->createForm(ServerTeamMemberType::class, $model);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $discordID     = null;
            $username      = '';
            $discriminator = 0;
            $avatarHash    = '';
            $modelUsername = $model->getUsername();

            if (is_numeric($modelUsername)) {
                try {
                    $user = $this->discord->fetchUser($modelUsername);
                    if (!$user) {
                        throw new Exception();
                    }
                    $discordID     = $user['id'];
                    $username      = $user['username'];
                    $avatarHash    = $user['avatar'];
                    $discriminator = (int)$user['discriminator'];
                } catch (Exception $e) {
                    $form
                        ->get('username')
                        ->addError(new FormError('User not found on Discord.'));
                }
            } else {
                try {
                    list($username, $discriminator) = $this->discord->extractUsernameAndDiscriminator($modelUsername);
                } catch (InvalidArgumentException $e) {
                    $form
                        ->get('username')
                        ->addError(new FormError('Invalid format. Must be username#discriminator.'));
                }
            }

            if ($username && $discriminator) {
                $user = $this->getUser();
                $teamMemberRepo = $this->em->getRepository(ServerTeamMember::class);
                if ($username === $user->getDiscordUsername() && $discriminator == $user->getDiscordDiscriminator()) {
                    $this->addFlash('danger', 'You cannot add yourself.');
                } else if ($teamMemberRepo->findByDiscordUsernameAndDiscriminator($username, $discriminator)) {
                    $this->addFlash('danger', 'User is already a member of the team.');
                } else {
                    $teamMember = (new ServerTeamMember())
                        ->setServer($server)
                        ->setRole($model->getRole())
                        ->setDiscordAvatar($avatarHash)
                        ->setDiscordUsername($username)
                        ->setDiscordDiscriminator($discriminator);
                    if ($discordID) {
                        $teamMember->setDiscordID($discordID);
                    }
                    $user = $this->em->getRepository(User::class)
                        ->findByDiscordUsernameAndDiscriminator($username, $discriminator);
                    if ($user) {
                        $teamMember->setUser($user);
                    }

                    $this->em->persist($teamMember);
                    $this->em->flush();
                    $this->addFlash('success', 'The user has been added to the server team');

                    $this->eventDispatcher->dispatch(
                        'app.server.action',
                        new ServerActionEvent($server, $user, 'Added team member.')
                    );

                    return new RedirectResponse(
                        $this->generateUrl('server_team', ['slug' => $slug])
                    );
                }
            }
        }

        $teamMembers = $this->em->getRepository(ServerTeamMember::class)->findByServer($server);

        return $this->render('server/team.html.twig', [
            'server'      => $server,
            'form'        => $form->createView(),
            'teamMembers' => $teamMembers
        ]);
    }

    /**
     * @Route("/server/settings/{slug}", name="settings")
     *
     * @param string  $slug
     * @param Request $request
     *
     * @return Response
     * @throws FileNotFoundException
     * @throws GuzzleException
     */
    public function settingsAction($slug, Request $request)
    {
        $server = $this->fetchServerOrThrow($slug);
        if (!$this->hasServerAccess($server, self::SERVER_ROLE_MANAGER)) {
            throw $this->createAccessDeniedException();
        }

        $user      = $this->getUser();
        $slug      = $server->getSlug();
        $discordID = $server->getDiscordID();
        $form      = $this->createForm(ServerType::class, $server, [
            'user' => $user
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // These values cannot be changed when editing.
            $server->setUser($user);
            $server->setSlug($slug);
            $server->setDiscordID($discordID);

            if ($this->processForm($form, true)) {
                $this->em->persist($server);
                $this->em->flush();
                $this->addFlash('success', 'The server has been updated.');

                $this->eventDispatcher->dispatch(
                    'app.server.action',
                    new ServerActionEvent($server, $user, 'Changed settings.')
                );

                return new RedirectResponse($this->generateUrl('profile_index'));
            } else {
                $this->addFlash('danger', 'Please fix the errors below.');
            }
        }

        return $this->render(
            'server/settings.html.twig',
            [
                'form'      => $form->createView(),
                'server'    => $server,
                'isEditing' => true
            ]
        );
    }

    /**
     * @Route("/server/add", name="add", options={"expose"=true})
     *
     * @param Request $request
     *
     * @return Response
     * @throws FileNotFoundException
     * @throws GuzzleException
     */
    public function addAction(Request $request)
    {
        $user   = $this->getUser();
        $server = new Server();
        $form   = $this->createForm(ServerType::class, $server, [
            'user' => $user
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $server->setUser($user);

            if ($this->processForm($form, false)) {
                $widget = $this->discord->fetchWidget($server->getDiscordID());
                $server->setMembersOnline(count($widget['members']));

                $teamMember = (new ServerTeamMember())
                    ->setUser($user)
                    ->setServer($server)
                    ->setRole(ServerTeamMember::ROLE_OWNER)
                    ->setDiscordUsername($user->getDiscordUsername())
                    ->setDiscordDiscriminator($user->getDiscordDiscriminator());
                $this->em->persist($server);
                $this->em->persist($teamMember);
                $this->em->flush();
                $this->addFlash('success', 'The server has been added.');

                $this->eventDispatcher->dispatch(
                    'app.server.action',
                    new ServerActionEvent($server, $user, 'Created server.')
                );

                return new RedirectResponse($this->generateUrl('profile_index'));
            } else {
                $this->addFlash('danger', 'Please fix the errors below.');
            }
        }

        return $this->render(
            'server/add.html.twig',
            [
                'form'      => $form->createView(),
                'isEditing' => false
            ]
        );
    }

    /**
     * @param FormInterface $form
     * @param bool          $isEditing
     *
     * @return bool
     * @throws FileNotFoundException
     * @throws Exception
     * @throws GuzzleException
     */
    private function processForm(FormInterface $form, $isEditing)
    {
        /** @var Server $server */
        $server   = $form->getData();
        $repo     = $this->getDoctrine()->getRepository(Server::class);
        $isValid  = true;

        if ($this->em->getRepository(BannedServer::class)->isBanned($server->getDiscordID())) {
            $form
                ->get('discordID')
                ->addError(new FormError('Server is banned.'));
            return false;
        }

        $bannedWordRepo = $this->em->getRepository(BannedWord::class);
        foreach($server->getTags() as $tag) {
            if ($bannedWordRepo->containsBannedWords($tag->getName())) {
                $form
                    ->get('tags')
                    ->addError(new FormError('Contains banned words.'));
                return false;
            }
        }
        if ($bannedWordRepo->containsBannedWords($server->getSummary())) {
            $form
                ->get('summary')
                ->addError(new FormError('Contains banned words.'));
            return false;
        }
        if ($bannedWordRepo->containsBannedWords($server->getDescription())) {
            $form
                ->get('description')
                ->addError(new FormError('Contains banned words.'));
            return false;
        }

        if (!$isEditing) {
            if ($repo->findByDiscordID($server->getDiscordID())) {
                $isValid = false;
                $form
                    ->get('discordID')
                    ->addError(new FormError('Discord ID in use.'));
            }

            if ($repo->findBySlug($server->getSlug())) {
                $isValid = false;
                $form
                    ->get('slug')
                    ->addError(new FormError('Slug already in use.'));
            }

            try {
                $this->discord->fetchWidget($server->getDiscordID());
            } catch(Exception $e) {
                $isValid = false;
                $this->addFlash('danger', 'Widget not enabled.');
            }
        }

        if (in_array($server->getSlug(), $this->getForbiddenSlugs())) {
            $isValid = false;
            $form
                ->get('slug')
                ->addError(new FormError('Slug already in use.'));
        }

        if ($form['updatePassword']->getData()) {
            if ($server->getServerPassword() === '') {
                $server->setServerPassword('');
            } else {
                $server->setServerPassword(
                    password_hash($server->getServerPassword(), PASSWORD_BCRYPT)
                );
            }
        } else {
            $server->setServerPassword('');
        }

        if (!$server->getBotInviteChannelID()) {
            $widget = [];
            try {
                $widget = $this->discord->fetchWidget($server->getDiscordID());
            } catch (Exception $e) {
                $this->logger->error($e->getMessage());
            }
            if (!$widget || !isset($widget['instant_invite'])) {
                $isValid = false;
                $form
                    ->get('botInviteChannelID')
                    ->addError(new FormError('Instant invite not enabled. A channel is required.'));
            }
        }

        $iconMedia   = null;
        $bannerMedia = null;
        try {
            $guild    = $this->discord->fetchGuild($server->getDiscordID());
            $iconFile = $this->discord->writeGuildIcon($server->getDiscordID(), $guild['icon']);
            if ($iconFile) {
                $iconMedia = $this->moveIconFile($iconFile, $server);
                if ($iconMedia) {
                    $server->setIconMedia($iconMedia);
                } else {
                    $isValid = false;
                    $this->addFlash('danger', 'There was an error grabbing the server icon image.');
                }
            }

            if ($bannerFile  = $form['bannerFile']->getData()) {
                $bannerCropData = $form['bannerCropData']->getData();
                if ($bannerCropData) {
                    $bannerCropData = json_decode($bannerCropData, true);
                }
                $bannerMedia = $this->moveBannerFile($bannerFile, $bannerCropData, $server);
                if ($bannerMedia) {
                    $server->setBannerMedia($bannerMedia);
                } else {
                    $isValid = false;
                    $form
                        ->get('bannerFile')
                        ->addError(new FormError('There was an error uploading the file.'));
                }
            }
        } catch (Exception $e) {
            if ($iconMedia) {
                $this->deleteUploadedFile($iconMedia);
            }
            if ($bannerMedia) {
                $this->deleteUploadedFile($bannerMedia);
            }
            throw $e;
        }

        return $isValid;
    }

    /**
     * Returns all the site paths which might conflict with server slugs
     *
     * @return array
     */
    private function getForbiddenSlugs()
    {
        $paths = [
            'admin'
        ];

        foreach ($this->get('router')->getRouteCollection()->all() as $route) {
            $path = $route->getPath();
            $path = array_filter(explode('/', $path));
            $path = array_shift($path);
            if ($path && !in_array($path, $paths) && $path[0] !== '{') {
                $paths[] = $path;
            }
        }

        return $paths;
    }

    /**
     * @param UploadedFile $file
     * @param array        $cropData
     * @param Server       $server
     *
     * @return Media
     * @throws Exception
     * @throws FileExistsException
     * @throws FileNotFoundException
     * @throws WriteException
     */
    private function moveBannerFile(UploadedFile $file, $cropData, Server $server)
    {
        if ($file->getError() !== 0) {
            return null;
        }

        $mimeTypes = [
            'image/jpeg' => 'jpg',
            'image/jpg'  => 'jpg',
            'image/png'  => 'png'
        ];
        $mimeType  = $file->getMimeType();
        if (!in_array($mimeType, array_keys($mimeTypes))) {
            return null;
        }

        if ($cropData) {
            $resizer = new ImageResize($file->getPathname());
            $resizer->freecrop($cropData['width'], $cropData['height'], $cropData['x'], $cropData['y']);
            $resizer->save($file->getPathname());
        }

        $paths = new Paths();
        $path  = $paths->getPathByType(
            'banner',
            $server->getDiscordID(),
            $this->snowflakeGenerator->generate(),
            $mimeTypes[$mimeType]
        );

        return $this->webHandler->write('banner', $path, $file->getPathname());
    }

    /**
     * @param string $filename
     * @param Server $server
     *
     * @return Media
     * @throws FileExistsException
     * @throws FileNotFoundException
     * @throws WriteException
     */
    private function moveIconFile($filename, Server $server)
    {
        $paths = new Paths();
        $path  = $paths->getPathByType(
            'icon',
            $server->getDiscordID(),
            $this->snowflakeGenerator->generate(),
            'png'
        );

        return $this->webHandler->write('icon', $path, $filename);
    }

    /**
     * @param Media $media
     *
     * @return bool
     * @throws FileNotFoundException
     */
    private function deleteUploadedFile(Media $media)
    {
        return $this->webHandler->getAdapter()->remove($media->getPath());
    }
}
