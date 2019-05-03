<?php
namespace App\Controller;

use App\Entity\BumpPeriod;
use App\Entity\BumpPeriodVote;
use App\Entity\Purchase;
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
        /** @var Server $premiumServer */
        $voteRepo       = $this->em->getRepository(BumpPeriodVote::class);
        $bumpPeriodNext = $this->em->getRepository(BumpPeriod::class)->findNextPeriod();

        $servers = $this->em->getRepository(Server::class)
            ->findByTeamMemberUser($this->getUser());

        // Set the server with the greatest premium status, which is used
        // to display the "Bump all servers" button if applicable.
        $premiumServer = null;
        $premiumStatus = Server::STATUS_STR_STANDARD;
        foreach($servers as $server) {
            $server->setLastBumpPeriodVote(
                $voteRepo->findLastBump($server)
            );
            if (!$premiumServer) {
                $premiumServer = $server;
            } else if ($server->getPremiumStatus() > $premiumServer->getPremiumStatus()) {
                $premiumServer = $server;
            }
        }
        if ($premiumServer) {
            $premiumStatus = $premiumServer->getPremiumStatusString();
        }

        return $this->render('profile/index.html.twig', [
            'servers'        => $servers,
            'premiumStatus'  => $premiumStatus,
            'bumpPeriodNext' => $bumpPeriodNext->getFormattedDate(),
            'title'          => 'Profile'
        ]);
    }

    /**
     * @Route("/profile/settings", name="settings")
     *
     * @return Response
     */
    public function settingsAction()
    {
        return $this->render('profile/settings.html.twig', [
            'title' => 'Profile Settings'
        ]);
    }

    /**
     * @Route("/profile/invoices", name="invoices")
     */
    public function invoicesAction()
    {
        $purchases = $this->em->getRepository(Purchase::class)->findByUser($this->getUser());

        return $this->render('profile/invoices.html.twig', [
            'purchases' => $purchases,
            'title'     => 'Invoices'
        ]);
    }
}
