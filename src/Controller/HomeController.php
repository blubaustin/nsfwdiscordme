<?php
namespace App\Controller;

use App\Repository\ServerRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(name="home_")
 */
class HomeController extends Controller
{
    /**
     * @Route("/", name="index")
     *
     * @param ServerRepository $serverRepository
     *
     * @return Response
     */
    public function indexAction(ServerRepository $serverRepository)
    {
        $servers = $serverRepository->findByRecent();

        return $this->render('home/index.html.twig', [
            'servers' => $servers
        ]);
    }

    /**
     * @Route("/category/{name}", name="category")
     *
     * @param string $name
     */
    public function categoryAction($name)
    {
        die($name);
    }
}
