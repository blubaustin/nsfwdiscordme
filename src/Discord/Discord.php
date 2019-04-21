<?php
namespace App\Discord;

use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use GuzzleHttp\Client as Guzzle;

/**
 * Class Discord
 */
class Discord
{
    const BASE_URL = 'https://discordapp.com/api/v6';

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
     * @param $method
     * @param $path
     *
     * @return mixed
     * @throws GuzzleException
     */
    protected function doRequest($method, $path)
    {
        $client = new Guzzle([
            'timeout' => 2.0
        ]);
        $response = $client->request($method, $this->buildURL($path));

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
