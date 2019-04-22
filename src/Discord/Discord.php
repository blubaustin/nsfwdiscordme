<?php
namespace App\Discord;

use App\Entity\AccessToken;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use GuzzleHttp\Client as Guzzle;

/**
 * Class Discord
 */
class Discord
{
    const BASE_URL   = 'https://discordapp.com/api/v6';
    const USER_AGENT = 'DiscordBot (http://dev.nsfwdiscordme.com/, 1)';
    const TIMEOUT    = 2.0;

    /**
     * @var string
     */
    protected $clientID;

    /**
     * @var string
     */
    protected $clientSecret;

    /**
     * @var string
     */
    protected $botToken;

    /**
     * @var AdapterInterface
     */
    protected $cache;

    /**
     * Constructor
     *
     * @param AdapterInterface $cache
     * @param string $clientID
     * @param string $clientSecret
     * @param string $botToken
     */
    public function __construct(AdapterInterface $cache, $clientID, $clientSecret, $botToken)
    {
        $this->cache        = $cache;
        $this->clientID     = $clientID;
        $this->clientSecret = $clientSecret;
        $this->botToken     = $botToken;
    }

    /**
     * @param int|string $serverID
     *
     * @return array
     * @throws GuzzleException
     */
    public function fetchWidget($serverID)
    {
        return $this->doRequest('GET', "guilds/${serverID}/widget.json");
    }

    /**
     * @param AccessToken $token
     *
     * @return array
     * @throws GuzzleException
     */
    public function fetchMeGuilds(AccessToken $token)
    {
        return $this->doRequest('GET', 'users/@me/guilds', $token);
    }

    /**
     * @param string $serverID
     *
     * @return array
     * @throws GuzzleException
     */
    public function fetchGuildChannels($serverID)
    {
        return $this->doRequest('GET', "guilds/${serverID}/channels", true);
    }

    /**
     * @param string $method
     * @param string $path
     * @param AccessToken|bool $token
     *
     * @return mixed
     * @throws GuzzleException
     */
    protected function doRequest($method, $path, $token = null)
    {
        $client = new Guzzle([
            'timeout' => self::TIMEOUT
        ]);

        $headers = [
            'User-Agent'   => self::USER_AGENT,
            'Accept'       => 'application/json',
            'Content-Type' => 'application/json'
        ];
        if ($token) {
            if ($token instanceof AccessToken) {
                $headers['Authorization'] = sprintf('%s %s', $token->getType(), $token->getToken());
            } else {
                $headers['Authorization'] = sprintf('Bot %s', $this->botToken);
            }
        }

        $response = $client->request($method, $this->buildURL($path), [
            'http_errors' => false,
            'headers'     => $headers
        ]);

        return json_decode((string)$response->getBody(), true);
    }

    /**
     * @param string $path
     *
     * @return string
     */
    private function buildURL($path)
    {
        return sprintf('%s/%s', self::BASE_URL, $path);
    }
}
