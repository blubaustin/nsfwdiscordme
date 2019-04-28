<?php
namespace App\Controller;

use App\Entity\ServerBumpEvent;
use App\Entity\ServerJoinEvent;
use App\Entity\Server;
use App\Entity\ServerFollow;
use App\Http\Request;
use DateTime;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr\Join;
use Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(name="home_")
 */
class HomeController extends Controller
{
    const CACHE_LIFETIME = 1800; // 30 minutes

    /**
     * @Route("/", name="index")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $masterServer = null;
        if ($request->query->get('page', 1) == 1) {
            $masterServer = $this->em->getRepository(Server::class)
                ->findOneBy(['premiumStatus' => Server::STATUS_MASTER]);
        }

        $query = $this->em->getRepository(Server::class)
            ->createQueryBuilder('s')
            ->where('s.isEnabled = 1')
            ->andWhere('s.isPublic = 1')
            ->andWhere('s.premiumStatus != :status')
            ->setParameter(':status', Server::STATUS_MASTER)
            ->orderBy('s.premiumStatus', 'desc')
            ->addOrderBy('s.bumpPoints', 'desc')
            ->getQuery()
            ->useResultCache(true, self::CACHE_LIFETIME);

        return $this->render('home/index.html.twig', [
            'sort'         => 'most-bumped',
            'masterServer' => $masterServer,
            'servers'      => $this->paginate($query)
        ]);
    }

    /**
     * @Route("/following", name="following")
     *
     * @return Response
     */
    public function followingAction()
    {
        $user = $this->getUser();
        if (!$user) {
            return new RedirectResponse($this->generateUrl('login'));
        }

        $query = $this->em->getRepository(Server::class)
            ->createQueryBuilder('s')
            ->leftJoin(ServerFollow::class, 'f', Join::WITH, 'f.server = s')
            ->where('s.isEnabled = 1')
            ->andWhere('s.isPublic = 1')
            ->andWhere('f.user = :user')
            ->setParameter(':user', $user)
            ->orderBy('f.id', 'desc');

        return $this->render('home/index.html.twig', [
            'sort'    => 'following',
            'servers' => $this->paginate($query)
        ]);
    }

    /**
     * @Route("/recently-bumped", name="recently_bumped")
     *
     * @return Response
     */
    public function recentlyBumpedAction()
    {
        $query = $this->em->getRepository(Server::class)
            ->createQueryBuilder('s')
            ->leftJoin(ServerBumpEvent::class, 'b', Join::WITH, 'b.server = s')
            ->where('s.isEnabled = 1')
            ->andWhere('s.isPublic = 1')
            ->orderBy('s.premiumStatus', 'desc')
            ->addOrderBy('b.id', 'desc')
            ->getQuery()
            ->useResultCache(true, self::CACHE_LIFETIME);

        return $this->render('home/index.html.twig', [
            'sort'    => 'recently-bumped',
            'servers' => $this->paginate($query)
        ]);
    }

    /**
     * @Route("/recently-added", name="recently_added")
     *
     * @return Response
     */
    public function recentlyAddedAction()
    {
        $query = $this->em->getRepository(Server::class)
            ->createQueryBuilder('s')
            ->where('s.isEnabled = 1')
            ->andWhere('s.isPublic = 1')
            ->orderBy('s.id', 'desc')
            ->getQuery();

        return $this->render('home/index.html.twig', [
            'sort'    => 'recently-added',
            'servers' => $this->paginate($query)
        ]);
    }

    /**
     * @Route("/trending", name="trending")
     *
     * @return Response
     * @throws Exception
     */
    public function trendingAction()
    {
        $query = $this->em->getRepository(Server::class)
            ->createQueryBuilder('s')
            ->leftJoin(ServerJoinEvent::class, 'j', Join::WITH, 'j.server = s')
            ->where('s.isEnabled = 1')
            ->andWhere('s.isPublic = 1')
            ->andWhere('j.dateCreated > :then')
            ->setParameter(':then', new DateTime('24 hours ago'))
            ->orderBy('j.id', 'desc')
            ->getQuery()
            ->useResultCache(true, self::CACHE_LIFETIME);

        return $this->render('home/index.html.twig', [
            'sort'    => 'trending',
            'servers' => $this->paginate($query)
        ]);
    }

    /**
     * @Route("/most-online", name="most_online")
     *
     * @return Response
     */
    public function mostOnlineAction()
    {
        $query = $this->em->getRepository(Server::class)
            ->createQueryBuilder('s')
            ->where('s.isEnabled = 1')
            ->andWhere('s.isPublic = 1')
            ->orderBy('s.premiumStatus', 'desc')
            ->addOrderBy('s.membersOnline', 'desc')
            ->getQuery()
            ->useResultCache(true, self::CACHE_LIFETIME);

        return $this->render('home/index.html.twig', [
            'sort'    => 'most-online',
            'servers' => $this->paginate($query)
        ]);
    }

    /**
     * @Route("/random", name="random")
     *
     * @return Response
     * @throws DBALException
     * @throws NonUniqueResultException
     */
    public function randomAction()
    {
        // ORDER BY RAND() is bad, m'kay. This does the trick.
        $stmt = $this->em->getConnection()->prepare('
            SELECT MAX(`id`) FROM `server` WHERE `is_enabled` = 1 AND `is_active` = 1 LIMIT 1
        ');
        $stmt->execute();
        $randID = rand(1, $stmt->fetchColumn(0));

        $server = $this->em->getRepository(Server::class)
            ->createQueryBuilder('s')
            ->where('s.isEnabled = 1')
            ->andWhere('s.isPublic = 1')
            ->andWhere('s.id >= :id')
            ->setParameter(':id', $randID)
            ->orderBy('s.id', 'asc')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$server) {
            return new RedirectResponse('/');
        }

        return new RedirectResponse(
            $this->generateUrl('server_index', ['slug' => $server->getSlug()])
        );
    }

    /**
     * @Route("/privacy", name="privacy")
     *
     * @return Response
     */
    public function privacyAction()
    {
        return $this->render('home/privacy.html.twig');
    }

    /**
     * @Route("/terms", name="terms")
     *
     * @return Response
     */
    public function termsAction()
    {
        return $this->render('home/terms.html.twig');
    }
}
