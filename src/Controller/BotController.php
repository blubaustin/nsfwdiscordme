<?php
namespace App\Controller;

use App\Discord\Discord;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use RestCord\DiscordClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/bot", name="bot_")
 */
class BotController extends Controller
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
}
