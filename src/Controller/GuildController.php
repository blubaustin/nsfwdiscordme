<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(name="guild_")
 */
class GuildController extends Controller
{
    /**
     * @Route("/server/{name}", name="index")
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
