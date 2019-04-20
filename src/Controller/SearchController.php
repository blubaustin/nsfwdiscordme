<?php
namespace App\Controller;

use FOS\ElasticaBundle\Finder\PaginatedFinderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(name="search_")
 */
class SearchController extends Controller
{
    /**
     * @var PaginatedFinderInterface
     */
    protected $finder;

    /**
     * @param PaginatedFinderInterface $finder
     */
    public function setFinder(PaginatedFinderInterface $finder)
    {
        $this->finder = $finder;
    }

    /**
     * @Route("/search", name="index")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $searchTerm = trim($request->query->get('q', ''));
        if (!$searchTerm) {
            throw $this->createNotFoundException();
        }

        $query = $this->finder->createPaginatorAdapter($searchTerm);

        return $this->render('search/index.html.twig', [
            'servers'    => $this->paginate($query),
            'searchTerm' => $searchTerm
        ]);
    }
}
