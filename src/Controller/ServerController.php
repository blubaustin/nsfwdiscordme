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

/**
 * @Route(name="server_")
 */
class ServerController extends Controller
{
    /**
     * @Route("/server/add", name="add")
     *
     * @param Request $request
     *
     * @return Response
     * @throws Exception
     */
    public function addAction(Request $request)
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
     * @Route("/s/{slug}", name="index")
     *
     * @param string $slug
     *
     * @return Response
     */
    public function indexAction($slug)
    {
        echo $slug;die();
    }
}
