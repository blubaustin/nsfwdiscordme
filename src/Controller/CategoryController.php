<?php
namespace App\Controller;

use App\Entity\Category;
use App\Entity\Server;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(name="category_")
 */
class CategoryController extends Controller
{
    /**
     * @Route("/category/{slug}", name="index")
     *
     * @param string $slug
     *
     * @return Response
     */
    public function indexAction($slug)
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
            ->setParameter(':category', $category)
            ->orderBy('s.bumpPoints', 'desc');

        return $this->render('category/index.html.twig', [
            'servers'  => $this->paginate($query),
            'category' => $category
        ]);
    }
}
