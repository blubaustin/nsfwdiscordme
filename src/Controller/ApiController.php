<?php
namespace App\Controller;

use App\Discord\Discord;
use App\Entity\Server;
use App\Http\Request;
use App\Services\RecaptchaService;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use RestCord\DiscordClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/v1", name="api_", options={"expose"=true})
 */
class ApiController extends Controller
{
    /**
     * @Route("/widget/{serverID}", name="widget")
     *
     * @param string $serverID
     * @param Discord $discord
     *
     * @return Response
     * @throws GuzzleException
     */
    public function widgetAction($serverID, Discord $discord)
    {
        try {
            $resp = $discord->fetchWidget($serverID);
        } catch (Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }

        return new JsonResponse($resp);
    }

    /**
     * @Route("/guild/{serverID}", name="guild")
     *
     * @param string        $serverID
     * @param DiscordClient $client
     *
     * @return JsonResponse
     */
    public function guildAction($serverID, DiscordClient $client)
    {
        $resp = $client->guild->getGuild(['guild.id' => (int)$serverID]);

        return new JsonResponse((array)$resp);
    }

    /**
     * @Route("/bump/{serverID}", name="bump", methods={"POST"})
     *
     * @param Request $request
     * @param int     $serverID
     *
     * @return JsonResponse
     */
    public function bumpAction(Request $request, $serverID)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $server = $this->em->getRepository(Server::class)->findByDiscordID($serverID);
        if (!$server) {
            throw $this->createNotFoundException();
        }
        if ($server->getUser()->getId() !== $this->getUser()->getId()) {
            throw $this->createAccessDeniedException();
        }

        // Was saved in recaptchaVerifyAction() when verifying the recaptcha.
        if ($request->getSession()->get('recaptcha_id') != $serverID) {
            throw $this->createAccessDeniedException();
        }

        $server->incrementBumpPoints();
        $this->em->flush();

        return new JsonResponse([
            'message'    => 'ok',
            'bumpPoints' => $server->getBumpPoints()
        ]);
    }

    /**
     * @Route("/recaptcha-verify", name="recaptcha_verify", methods={"POST"})
     *
     * @param Request          $request
     * @param RecaptchaService $recaptchaService
     *
     * @return JsonResponse
     * @throws GuzzleException
     */
    public function recaptchaVerifyAction(Request $request, RecaptchaService $recaptchaService)
    {
        $id    = $request->request->get('id');
        $token = $request->request->get('token');
        if (!$id || !$token) {
            throw $this->createNotFoundException();
        }

        if ($recaptchaService->verify($token)) {
            $request->getSession()->set('recaptcha_id', $id);

            return new JsonResponse([
                'success' => true
            ]);
        }

        return new JsonResponse([
            'success' => false
        ]);
    }
}
