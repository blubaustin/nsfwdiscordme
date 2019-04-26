<?php
namespace App\Controller;

use App\Entity\BumpPeriod;
use App\Entity\BumpPeriodVote;
use App\Entity\Server;
use App\Entity\ServerTeamMember;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(name="profile_")
 */
class ProfileController extends Controller
{
    /**
     * @Route("/profile", name="index", options={"expose"=true})
     *
     * @return Response
     * @throws NonUniqueResultException
     * @throws DBALException
     */
    public function indexAction()
    {
        /** @var Server $premiumServer */
        $premiumServer  = null;
        $voteRepo       = $this->em->getRepository(BumpPeriodVote::class);
        $bumpPeriodNext = $this->em->getRepository(BumpPeriod::class)->findNextPeriod();

        /** @var Server[] $servers */
        $servers = $this->em->getRepository(Server::class)
            ->createQueryBuilder('s')
            ->leftJoin(ServerTeamMember::class, 't', Join::WITH, 't.server = s')
            ->where('t.user = :user')
            ->setParameter(':user', $this->getUser())
            ->getQuery()
            ->execute();

        foreach($servers as $server) {
            $server->lastBump = $voteRepo->findLastBump($server);
            if (!$premiumServer) {
                $premiumServer = $server;
            } else if ($server->getPremiumStatus() > $premiumServer->getPremiumStatus()) {
                $premiumServer = $server;
            }
        }

        $premiumStatus = Server::STATUS_STR_STANDARD;
        if ($premiumServer) {
            $premiumStatus = $premiumServer->getPremiumStatusString();
        }

        return $this->render('profile/index.html.twig', [
            'servers'        => $servers,
            'premiumStatus'  => $premiumStatus,
            'bumpPeriodNext' => $bumpPeriodNext->getFormattedDate()
        ]);
    }

    /**
     * @Route("/profile/settings", name="settings")
     *
     * @return Response
     */
    public function settingsAction()
    {
        return $this->render('profile/settings.html.twig');
    }
}
