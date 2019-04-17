<?php
namespace App\Controller;

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
     */
    public function indexAction()
    {
        return $this->render('profile/index.html.twig');
    }
}
