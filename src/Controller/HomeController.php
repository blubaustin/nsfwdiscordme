<?php
namespace App\Controller;

use App\Entity\Server;
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
     * @return Response
     */
    public function indexAction()
    {
        $query = $this->em->getRepository(Server::class)
            ->createQueryBuilder('s')
            ->where('s.isEnabled = 1')
            ->orderBy('s.bumpPoints', 'desc');

        return $this->render('home/index.html.twig', [
            'servers' => $this->paginate($query)
        ]);
    }
}
