<?php
namespace App\Controller;

use App\Repository\CategoryRepository;
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
     * @Route("/category/{slug}", name="category")
     *
     * @param string             $slug
     * @param CategoryRepository $categoryRepository
     * @param ServerRepository   $serverRepository
     *
     * @return Response
     */
    public function categoryAction($slug, CategoryRepository $categoryRepository, ServerRepository $serverRepository)
    {
        $category = $categoryRepository->findBySlug($slug);
        if (!$category) {
            throw $this->createNotFoundException();
        }

        $servers = $serverRepository->findByCategory($category);

        return $this->render('home/category.html.twig', [
            'servers'  => $servers,
            'category' => $category
        ]);
    }
}
