<?php
namespace App\Controller;

use Elastica\Query;
use Elastica\Query\QueryString;
use FOS\ElasticaBundle\Finder\PaginatedFinderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(name="search_")
 */
class SearchController extends Controller
{
    const ORDER_FIELDS = [
        'bumpPoints'
    ];

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
        $orderField = trim($request->query->get('order', 'bumpPoints'));
        if (!$searchTerm || !in_array($orderField, self::ORDER_FIELDS)) {
            throw $this->createNotFoundException();
        }

        $query = new Query();
        $query->addSort([
            'premiumStatus' => [
                'order'         => 'desc',
                'unmapped_type' => 'long'
            ],
            $orderField => [
                'order'         => 'desc',
                'unmapped_type' => 'long'
            ]
        ]);
        $query->setQuery(new QueryString($searchTerm));

        $query = $this->finder->createPaginatorAdapter($query);

        return $this->render('search/index.html.twig', [
            'servers'    => $this->paginate($query),
            'searchTerm' => $searchTerm
        ]);
    }
}
