<?php
namespace App\Controller;

use App\Entity\BumpPeriod;
use App\Entity\Server;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(name="profile_")
 */
class ProfileController extends Controller
{
    /**
     * @Route("/profile", name="index")
     *
     * @return Response
     * @throws NonUniqueResultException
     */
    public function indexAction()
    {
        $bumpPeriodNext = $this->em->getRepository(BumpPeriod::class)->findNextPeriod();
        $servers        = $this->em->getRepository(Server::class)->findByUser($this->getUser());

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
