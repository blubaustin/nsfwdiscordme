<?php
namespace App\Controller;

use App\Entity\Server;
use App\Entity\ServerEvent;
use App\Http\Request;
use DateInterval;
use DateTime;
use EasyCorp\Bundle\EasyAdminBundle\Controller\EasyAdminController;
use Elastica\Aggregation\DateHistogram;
use Elastica\Query;
use Exception;
use FOS\ElasticaBundle\Finder\PaginatedFinderInterface;
use FOS\ElasticaBundle\Paginator\FantaPaginatorAdapter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin")
 */
class AdminController extends EasyAdminController
{
    /**
     * @var PaginatedFinderInterface
     */
    protected $eventsFinder;

    /**
     * @param PaginatedFinderInterface $eventsFinder
     *
     * @return $this
     */
    public function setEventsFinder(PaginatedFinderInterface $eventsFinder)
    {
        $this->eventsFinder = $eventsFinder;

        return $this;
    }

/*    protected function createNewServerEntity()
    {

    }

    protected function persistServerEntity()
    {

    }

    protected function updateServerEntity()
    {
        die('here');
    }*/

    /**
     * @Route("/stats")
     *
     * @param Request $request
     *
     * @return Response
     * @throws Exception
     */
    public function statsAction(Request $request)
    {
        if ($request->getMethod() === 'POST') {
            /** @var FantaPaginatorAdapter $adapter */
            $query   = $this->createServerEventQuery(ServerEvent::TYPE_JOIN);
            $results = $this->eventsFinder->findPaginated($query);
            $adapter = $results->getAdapter();
            $buckets = $adapter->getAggregations()['hits']['buckets'];
            $joins   = $this->generateStatsFromBuckets($buckets);

            $query   = $this->createServerEventQuery(ServerEvent::TYPE_VIEW);
            $results = $this->eventsFinder->findPaginated($query);
            $adapter = $results->getAdapter();
            $buckets = $adapter->getAggregations()['hits']['buckets'];
            $views   = $this->generateStatsFromBuckets($buckets);

            $query   = $this->createServerEventQuery(ServerEvent::TYPE_BUMP);
            $results = $this->eventsFinder->findPaginated($query);
            $adapter = $results->getAdapter();
            $buckets = $adapter->getAggregations()['hits']['buckets'];
            $bumps   = $this->generateStatsFromBuckets($buckets);

            return new JsonResponse([
                'message' => 'ok',
                'joins'   => $joins,
                'views'   => $views,
                'bumps'   => $bumps
            ]);
        }

        return $this->render('admin/stats.html.twig');
    }

    /**
     * @param int $eventType
     *
     * @return Query
     */
    private function createServerEventQuery($eventType)
    {
        /** @var FantaPaginatorAdapter $adapter */
        $query = new Query();
        $query->setSize(0);

        $bool = new Query\BoolQuery();
        $bool->addMust(new Query\Term([
            'eventType' => $eventType
        ]));
        $query->setQuery($bool);
        $query->addAggregation(new DateHistogram('hits', 'dateCreated', 'day'));

        return $query;
    }

    /**
     * @param array $buckets
     *
     * @return array
     * @throws Exception
     */
    private function generateStatsFromBuckets(array $buckets)
    {
        $rows = [];
        foreach($buckets as $bucket) {
            $day = (new DateTime($bucket['key_as_string']))->format('Y-m-d');
            $rows[$day] = $bucket['doc_count'];
        }

        $final = [];
        $now   = new DateTime('30 days ago');
        $int   = new DateInterval('P1D');
        for($i = 30; $i > 0; $i--) {
            $day = $now->add($int)->format('Y-m-d');
            if (isset($rows[$day])) {
                $final[] = [
                    'day'   => $day,
                    'count' => $rows[$day]
                ];
            } else {
                $final[] = [
                    'day'   => $day,
                    'count' => 0
                ];
            }
        }

        return $final;
    }
}
