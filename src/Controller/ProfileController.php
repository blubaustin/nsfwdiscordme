<?php
namespace App\Controller;

use App\Repository\ServerRepository;
use App\Services\RecaptchaService;
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
     * @param ServerRepository $serverRepository
     *
     * @return Response
     */
    public function indexAction(ServerRepository $serverRepository)
    {
        $servers = $serverRepository->findByUser($this->getUser());

        return $this->render('profile/index.html.twig', [
            'servers' => $servers
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
