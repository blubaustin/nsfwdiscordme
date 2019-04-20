<?php
namespace App\Controller;

use App\Entity\Category;
use App\Entity\Server;
use Symfony\Component\HttpFoundation\Request;
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
            ->where('s.isEnabled = 1');

        return $this->render('home/index.html.twig', [
            'servers' => $this->paginate($query)
        ]);
    }

    /**
     * @Route("/category/{slug}", name="category")
     *
     * @param string $slug
     *
     * @return Response
     */
    public function categoryAction($slug)
    {
        $category = $this->em->getRepository(Category::class)->findBySlug($slug);
        if (!$category) {
            throw $this->createNotFoundException();
        }

        $query = $this->em->getRepository(Server::class)
            ->createQueryBuilder('s')
            ->leftJoin('s.categories', 'category')
            ->where('s.isEnabled = 1')
            ->andWhere('category = :category')
            ->setParameter(':category', $category);

        return $this->render('home/category.html.twig', [
            'servers'  => $this->paginate($query),
            'category' => $category
        ]);
    }

    /**
     * @Route("/search", name="search")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function searchAction(Request $request)
    {
        $query = $this->em->getRepository(Server::class)
            ->createQueryBuilder('s')
            ->where('s.isEnabled = 1');

        return $this->render('home/index.html.twig', [
            'servers' => $this->paginate($query)
        ]);
    }
}
