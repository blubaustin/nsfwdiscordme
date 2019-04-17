<?php
namespace App\Controller;

use App\Entity\Server;
use App\Form\Type\ServerType;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
        $form = $this->createForm(ServerType::class, $server);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            dump($server);die();
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
