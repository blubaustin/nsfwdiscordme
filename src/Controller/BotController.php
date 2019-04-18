<?php
namespace App\Controller;

use GuzzleHttp\Exception\GuzzleException;
use RestCord\DiscordClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use GuzzleHttp\Client as Guzzle;

/**
 * @Route("/bot", name="bot_")
 */
class BotController extends Controller
{
    /**
     * @Route("/widget/{serverID}", name="widget")
     *
     * @param string $serverID
     *
     * @return Response
     * @throws GuzzleException
     */
    public function widgetAction($serverID)
    {
        $client = new Guzzle([
            'timeout' => 2.0
        ]);
        $response = $client->request('GET', "https://discordapp.com/api/guilds/${serverID}/widget.json", [
            'http_errors' => false
        ]);

        return new JsonResponse((string)$response->getBody(), $response->getStatusCode(), [], true);
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
