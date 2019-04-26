<?php
namespace App\Controller;

use App\Http\Request;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(name="upgrade_")
 */
class UpgradeController extends Controller
{
    /**
     * @Route("/upgrade/{slug}", name="index")
     *
     * @param string  $slug
     *
     * @param Request $request
     *
     * @return Response
     * @throws Exception
     */
    public function indexAction($slug, Request $request)
    {
        $server = $this->fetchServerOrThrow($slug);
        if (!$this->hasServerAccess($server, self::SERVER_ROLE_MANAGER)) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('upgrade/index.html.twig', [
            'server' => $server
        ]);
    }
}
