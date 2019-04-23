<?php
namespace App\Controller;

use App\Entity\Media;
use App\Entity\Server;
use App\Form\Type\ServerType;
use App\Media\Adapter\Exception\FileExistsException;
use App\Media\Adapter\Exception\FileNotFoundException;
use App\Media\Adapter\Exception\WriteException;
use App\Media\Paths;
use App\Media\WebHandlerInterface;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
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
     * @param string $slug
     *
     * @return Response
     */
    public function indexAction($slug)
    {
        $server = $this->em->getRepository(Server::class)->findBySlug($slug);
        if (!$server || !$server->isEnabled()) {
            throw $this->createNotFoundException();
        }

        return $this->render('server/index.html.twig', [
            'server'  => $server,
            'isOwner' => $this->canManageServer($server)
        ]);
    }

    /**
     * @Route("/server/upgrade/{slug}", name="upgrade")
     *
     * @param string $slug
     *
     * @return Response
     */
    public function upgradeAction($slug)
    {
        die($slug);
    }

    /**
     * @Route("/server/stats/{slug}", name="stats")
     *
     * @param string $slug
     *
     * @return Response
     */
    public function statsAction($slug)
    {
        die($slug);
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
        $server = $this->em->getRepository(Server::class)->findBySlug($slug);
        if (!$server || !$this->canManageServer($server, 'settings')) {
            throw $this->createNotFoundException();
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

                return new RedirectResponse($this->generateUrl('profile_index'));
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
                $this->em->persist($server);
                $this->em->flush();
                $this->addFlash('success', 'The server has been added.');

                return new RedirectResponse($this->generateUrl('profile_index'));
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
            $server->setServerPassword(
                password_hash($server->getServerPassword(), PASSWORD_BCRYPT)
            );
        } else {
            $server->setServerPassword('');
        }

        if (!$server->getBotInviteChannelID()) {
            $isValid = false;
            $form
                ->get('botInviteChannelID')
                ->addError(new FormError('A channel is required.'));
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
                $bannerMedia = $this->moveBannerFile($bannerFile, $server);
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
     * @param Server       $server
     *
     * @return Media
     * @throws FileExistsException
     * @throws FileNotFoundException
     * @throws WriteException
     */
    private function moveBannerFile(UploadedFile $file, Server $server)
    {
        if ($file->getError() !== 0) {
            return null;
        }

        $mimeTypes = [
            'image/jpeg' => 'jpg',
            'image/jpg'  => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif'
        ];
        $mimeType  = $file->getMimeType();
        if (!in_array($mimeType, array_keys($mimeTypes))) {
            return null;
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
