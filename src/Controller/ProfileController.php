<?php
namespace App\Controller;

use App\Entity\BumpPeriod;
use App\Entity\BumpPeriodVote;
use App\Entity\Server;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\NonUniqueResultException;
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
        $voteRepo = $this->em->getRepository(BumpPeriodVote::class);

        $bumpPeriodNext = $this->em->getRepository(BumpPeriod::class)->findNextPeriod();
        $servers        = $this->em->getRepository(Server::class)->findByUser($this->getUser());
        foreach($servers as $server) {
            $server->lastBump = $voteRepo->findLastBump($server);
        }

        return $this->render('profile/index.html.twig', [
            'servers'        => $servers,
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
