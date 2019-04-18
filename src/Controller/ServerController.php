<?php
namespace App\Controller;

use App\Entity\Server;
use App\Form\Type\ServerType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Exception;
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
        die($slug);
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
     * @Route("/server/settings/{slug}", name="settings")
     *
     * @param string $slug
     *
     * @return Response
     */
    public function settingsAction($slug)
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
     * @Route("/server/add", name="add")
     *
     * @param Request         $request
     * @param RouterInterface $router
     *
     * @return Response
     * @throws Exception
     */
    public function addAction(Request $request, RouterInterface $router)
    {
        $server = new Server();
        $server->setUser($this->getUser());
        $form = $this->createForm(ServerType::class, $server);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $hasError = false;
            $repo = $this->getDoctrine()->getRepository(Server::class);

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

            if (in_array($server->getSlug(), $this->getForbiddenSlugs($router))) {
                $hasError = true;
                $form
                    ->get('slug')
                    ->addError(new FormError('Slug already in use.'));
            }

            if ($form['updatePassword']->getData()) {
                $encryptedPassword = password_hash($server->getServerPassword(), PASSWORD_BCRYPT);
                $server->setServerPassword($encryptedPassword);
            }

            if (!$hasError) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($server);
                $em->flush();
                $this->addFlash('success', 'The server has been added.');

                return new RedirectResponse($this->generateUrl('profile_index'));
            }
        }

        return $this->render('server/add.html.twig', [
            'form' => $form->createView()
        ]);
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

        foreach($router->getRouteCollection()->all() as $route) {
            $path = $route->getPath();
            $path = array_filter(explode('/', $path));
            $path = array_shift($path);
            if ($path && !in_array($path, $paths) && $path[0] !== '{') {
                $paths[] = $path;
            }
        }

        return $paths;
    }
}
