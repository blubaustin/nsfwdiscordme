<?php
namespace App\Controller;

use App\Discord\Discord;
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
use Symfony\Component\Routing\RouterInterface;

/**
 * @Route(name="server_")
 */
class ServerController extends Controller
{
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

        $server->setBotHumanCheck(true);

        return $this->render('server/index.html.twig', [
            'server' => $server
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
     * @param string              $slug
     * @param Request             $request
     * @param Discord             $discord
     * @param RouterInterface     $router
     * @param WebHandlerInterface $webHandler
     *
     * @return Response
     * @throws FileExistsException
     * @throws FileNotFoundException
     * @throws GuzzleException
     * @throws WriteException
     */
    public function settingsAction(
        $slug,
        Request $request,
        Discord $discord,
        RouterInterface $router,
        WebHandlerInterface $webHandler
    )
    {
        $server = $this->em->getRepository(Server::class)->findBySlug($slug);
        if (!$server || $server->getUser()->getId() !== $this->getUser()->getId()) {
            throw $this->createNotFoundException();
        }

        $slug      = $server->getSlug();
        $discordID = $server->getDiscordID();

        $form = $this->createForm(ServerType::class, $server);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $server->setSlug($slug);           // Ensure the slug wasn't changed
            $server->setDiscordID($discordID); // Ensure the ID wasn't changed

            $hasError = $this->processForm($form, true, $discord, $router, $webHandler);
            if (!$hasError) {
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
                'isEditing' => true
            ]
        );
    }

    /**
     * @Route("/server/add", name="add", options={"expose"=true})
     *
     * @param Request             $request
     * @param Discord             $discord
     * @param RouterInterface     $router
     * @param WebHandlerInterface $webHandler
     *
     * @return Response
     * @throws FileExistsException
     * @throws FileNotFoundException
     * @throws GuzzleException
     * @throws WriteException
     */
    public function addAction(
        Request $request,
        Discord $discord,
        RouterInterface $router,
        WebHandlerInterface $webHandler
    )
    {
        $server = new Server();
        $server->setUser($this->getUser());
        $form = $this->createForm(ServerType::class, $server);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $hasError = $this->processForm($form, false, $discord, $router, $webHandler);
            if (!$hasError) {
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
     * @param FormInterface       $form
     * @param bool                $isEditing
     * @param Discord             $discord
     * @param RouterInterface     $router
     * @param WebHandlerInterface $webHandler
     *
     * @return bool
     * @throws FileExistsException
     * @throws FileNotFoundException
     * @throws GuzzleException
     * @throws WriteException
     * @throws Exception
     */
    private function processForm(
        FormInterface $form,
        $isEditing,
        Discord $discord,
        RouterInterface $router,
        WebHandlerInterface $webHandler
    )
    {
        /** @var Server $server */
        $server   = $form->getData();
        $repo     = $this->getDoctrine()->getRepository(Server::class);
        $hasError = false;

        if (!$isEditing) {
            $found = $repo->findByDiscordID($server->getDiscordID());
            if ($found) {
                $hasError = true;
                $form
                    ->get('discordID')
                    ->addError(new FormError('Discord ID in use.'));
            }

            $found = $repo->findBySlug($server->getSlug());
            if ($found) {
                $hasError = true;
                $form
                    ->get('slug')
                    ->addError(new FormError('Slug already in use.'));
            }
        }

        if (in_array($server->getSlug(), $this->getForbiddenSlugs($router))) {
            $hasError = true;
            $form
                ->get('slug')
                ->addError(new FormError('Slug already in use.'));
        }

        try {
            $discord->fetchWidget($server->getDiscordID());
        } catch(Exception $e) {
            $hasError = true;
            $this->addFlash('danger', 'Widget not enabled.');
        }

        if ($form['updatePassword']->getData()) {
            $encryptedPassword = password_hash($server->getServerPassword(), PASSWORD_BCRYPT);
            $server->setServerPassword($encryptedPassword);
        }

        $iconMedia = null;
        $iconFile  = $form['iconFile']->getData();
        if ($iconFile) {
            $iconMedia = $this->moveUploadedFile($iconFile, $webHandler, $server->getDiscordID(), 'icon');
            if (!$iconMedia) {
                $hasError = true;
                $form
                    ->get('iconFile')
                    ->addError(new FormError('There was an error uploading the file.'));
            } else {
                $server->setIconMedia($iconMedia);
            }
        }

        $bannerMedia = null;
        $bannerFile  = $form['bannerFile']->getData();
        if ($bannerFile) {
            $bannerMedia = $this->moveUploadedFile($bannerFile, $webHandler, $server->getDiscordID(), 'banner');
            if (!$bannerMedia) {
                $hasError = true;
                $form
                    ->get('bannerFile')
                    ->addError(new FormError('There was an error uploading the file.'));
            } else {
                $server->setBannerMedia($bannerMedia);
            }
        }

        if ($hasError) {
            if ($iconMedia) {
                $this->deleteUploadedFile($iconMedia, $webHandler);
            }
            if ($bannerMedia) {
                $this->deleteUploadedFile($bannerMedia, $webHandler);
            }
        }

        return $hasError;
    }

    /**
     * Returns all the site paths which might conflict with server slugs
     *
     * @param RouterInterface $router
     *
     * @return array
     */
    private function getForbiddenSlugs(RouterInterface $router)
    {
        $paths = [
            'admin'
        ];

        foreach ($router->getRouteCollection()->all() as $route) {
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
     * @param UploadedFile        $file
     * @param WebHandlerInterface $webHandler
     * @param string              $serverID
     * @param string              $name
     *
     * @return Media
     * @throws FileExistsException
     * @throws FileNotFoundException
     * @throws WriteException
     */
    private function moveUploadedFile(UploadedFile $file, WebHandlerInterface $webHandler, $serverID, $name)
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
            $name,
            $serverID,
            $this->snowflakeGenerator->generate(),
            $mimeTypes[$mimeType]
        );

        return $webHandler->write($name, $path, $file->getPathname());
    }

    /**
     * @param Media               $media
     * @param WebHandlerInterface $webHandler
     *
     * @return bool
     * @throws FileNotFoundException
     */
    private function deleteUploadedFile(Media $media, WebHandlerInterface $webHandler)
    {
        return $webHandler->getAdapter()->remove($media->getPath());
    }
}
