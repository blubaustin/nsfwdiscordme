<?php
namespace App\Controller;

use App\Entity\Category;
use App\Entity\Server;
use App\Entity\Tag;
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
            ->andWhere('s.isPublic = 1')
            ->andWhere('category = :category')
            ->setParameter(':category', $category)
            ->orderBy('s.premiumStatus', 'desc')
            ->addOrderBy('s.bumpPoints', 'desc');

        return $this->render('category/index.html.twig', [
            'servers'  => $this->paginate($query),
            'category' => $category
        ]);
    }

    /**
     * @Route("/tag/{tag}", name="tag")
     *
     * @param string $tag
     *
     * @return Response
     */
    public function tagAction($tag)
    {
        $tag = $this->em->getRepository(Tag::class)->findByName($tag);
        if (!$tag) {
            throw $this->createNotFoundException();
        }

        $query = $this->em->getRepository(Server::class)
            ->createQueryBuilder('s')
            ->leftJoin('s.tags', 'tag')
            ->where('s.isEnabled = 1')
            ->andWhere('s.isPublic = 1')
            ->andWhere('tag = :tag')
            ->setParameter(':tag', $tag)
            ->orderBy('s.premiumStatus', 'desc')
            ->addOrderBy('s.bumpPoints', 'desc');

        return $this->render('category/index.html.twig', [
            'servers' => $this->paginate($query),
            'tag'     => $tag
        ]);
    }

    /**
     * @Route("/tags", name="tags")
     */
    public function tagsAction()
    {
        return $this->render('category/tags.html.twig', [
            'tags' => $this->em->getRepository(Tag::class)->findAll()
        ]);
    }
}
