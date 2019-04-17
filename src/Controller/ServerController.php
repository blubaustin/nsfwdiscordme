<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/server", name="server_")
 */
class ServerController extends Controller
{
    /**
     * @Route("/add", name="add")
     *
     * @return Response
     */
    public function addAction()
    {
        return $this->render('profile/index.html.twig');
    }

    /**
     * @Route("/{name}", name="index")
     *
     * @param string $name
     *
     * @return Response
     */
    public function indexAction($name)
    {
        echo $name;die();
    }
}
