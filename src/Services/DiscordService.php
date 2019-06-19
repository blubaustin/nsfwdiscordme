<?php
namespace App\Services;

use App\Entity\AccessToken;
use App\Services\Exception\DiscordException;
use App\Services\Exception\DiscordRateLimitException;
use Exception;
use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\CacheItem;

/**
 * Class DiscordService
 */
class DiscordService
{
    use LoggerAwareTrait;

    const API_BASE_URL = 'https://discordapp.com/api/v6';
    const CDN_BASE_URL = 'https://cdn.discordapp.com';
    const USER_AGENT   = 'nsfwdiscord.me (https://nsfwdiscord.me/, 1)';
    const TIMEOUT      = 2.0;
    const RETRY_LIMIT  = 3;
    const CACHE_TIME   = 10;

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
     * @var string
     */
    protected $defaultIcon;

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
     * @param string $defaultIcon
     */
    public function __construct(AdapterInterface $cache, $clientID, $clientSecret, $botToken, $defaultIcon)
    {
        $this->cache        = $cache;
        $this->clientID     = $clientID;
        $this->clientSecret = $clientSecret;
        $this->botToken     = $botToken;
        $this->defaultIcon  = $defaultIcon;
    }

    /**
     * @param int|string $serverID
     *
     * @return array
     * @throws GuzzleException
     * @throws DiscordRateLimitException
     */
    public function fetchWidget($serverID)
    {
        return $this->doRequest('GET', "guilds/${serverID}/widget.json");
    }

    /**
     * @param string|int $userID
     *
     * @return array
     * @throws GuzzleException
     * @throws DiscordRateLimitException
     */
    public function fetchUser($userID)
    {
        return $this->doRequest('GET', "users/${userID}", null, true);
    }

    /**
     * @param string $serverID
     *
     * @return array
     * @throws GuzzleException
     * @throws DiscordRateLimitException
     */
    public function fetchGuild($serverID)
    {
        return $this->doRequest('GET', "guilds/${serverID}", null, true);
    }

    /**
     * @param AccessToken $token
     *
     * @return array
     * @throws GuzzleException
     * @throws DiscordRateLimitException
     */
    public function fetchMeGuilds(AccessToken $token)
    {
        return $this->doRequest('GET', 'users/@me/guilds', null, $token);
    }

    /**
     * @param string $serverID
     *
     * @return array
     * @throws GuzzleException
     * @throws DiscordRateLimitException
     */
    public function fetchGuildChannels($serverID)
    {
        return $this->doRequest('GET', "guilds/${serverID}/channels", null, true);
    }

    /**
     * @param string $serverID
     *
     * @return array
     * @throws GuzzleException
     * @throws DiscordRateLimitException
     */
    public function fetchGuildMembers($serverID)
    {
        return $this->doRequest('GET', "guilds/${serverID}/members", null, true);
    }

    /**
     * @param string $serverID
     *
     * @return int
     * @throws Exception
     * @throws GuzzleException
     */
    public function fetchOnlineCount($serverID)
    {
        try {
            $widget = $this->fetchWidget($serverID);
            if (is_array($widget) && isset($widget['members'])) {
                return count($widget['members']);
            }
        } catch (Exception $e) {}

        try {
            $members = $this->fetchGuildMembers($serverID);
            if (is_array($members)) {
                return count($members);
            }
        } catch (Exception $e) {}

        throw new Exception("Unable to fetch online count for ${serverID}.");
    }

    /**
     * @param string $channelID
     *
     * @return string
     * @throws Exception
     * @throws GuzzleException
     */
    public function createBotInviteURL($channelID)
    {
        $invite = $this->doRequest('POST', "channels/${channelID}/invites", [], true);
        if (!$invite || !isset($invite['code'])) {
            throw new Exception('Unable to generate invite.');
        }

        return "https://discordapp.com/invite/${invite['code']}";
    }

    /**
     * @param string $serverID
     *
     * @return string
     * @throws Exception
     * @throws GuzzleException
     */
    public function createWidgetInviteURL($serverID)
    {
        $widget = $this->fetchWidget($serverID);
        if (!$widget || !isset($widget['instant_invite'])) {
            throw new Exception('Unable to generate invite.');
        }

        return $widget['instant_invite'];
    }

    /**
     * @param string $serverID
     * @param string $iconHash
     * @param string $ext
     *
     * @return string
     */
    public function writeGuildIcon($serverID, $iconHash, $ext = 'png')
    {
        if (!$iconHash) {
            $tmp = tempnam(sys_get_temp_dir(), 'icon_');
            file_put_contents($tmp, file_get_contents($this->defaultIcon));

            return $tmp;
        }

        $url    = sprintf('%s/icons/%s/%s.%s', self::CDN_BASE_URL, $serverID, $iconHash, $ext);
        $client = new Guzzle([
            'timeout' => self::TIMEOUT
        ]);
        $resp = $client->get($url);
        $data = (string)$resp->getBody();

        $tmp = tempnam(sys_get_temp_dir(), 'icon_');
        file_put_contents($tmp, $data);

        return $tmp;
    }

    /**
     * Given a username#discriminator combination, returns an array with separate username and discriminator
     *
     * Throws an InvalidArgumentException when the given username is not valid.
     *
     * @see https://discordapp.com/developers/docs/resources/user#usernames-and-nicknames
     *
     * @param string $username
     *
     * @return array
     */
    public function extractUsernameAndDiscriminator($username)
    {
        if (preg_match('/^([^@#:]{2,32})#([\d]{4})$/i', $username, $matches) && strpos($username, '```') === false) {
            $username      = $matches[1];
            $discriminator = (int)$matches[2];
            if (in_array(strtolower($username), ['discordtag', 'everyone', 'here'])) {
                throw new InvalidArgumentException(
                    "Invalid username ${username}."
                );
            }

            return [$username, $discriminator];
        }

        throw new InvalidArgumentException(
            "Invalid username ${username}."
        );
    }

    /**
     * @param string           $method
     * @param string           $path
     * @param array|null       $body
     * @param AccessToken|bool $token
     *
     * @return mixed
     * @throws GuzzleException
     * @throws DiscordRateLimitException
     * @throws Exception
     */
    protected function doRequest($method, $path, $body = null, $token = null)
    {
        $url       = sprintf('%s/%s', self::API_BASE_URL, $path);
        $cacheKey  = sprintf('discord.%s.%s', $method, md5($url));
        $cacheItem = null;

        try {
            $cacheItem = $this->cache->getItem($cacheKey);
            if ($cacheItem->isHit()) {
                return $cacheItem->get();
            }
        } catch (\Psr\Cache\InvalidArgumentException $e) {
            $this->logger->warning($e->getMessage());
        }

        $tries   = 0;
        $data    = [];
        $options = $this->buildRequestOptions($body, $token);
        $client  = new Guzzle();

        while(true) {
            $this->logger->debug(
                sprintf('Discord: %s %s', $method, $url),
                $options
            );

            $response   = $client->request($method, $url, $options);
            $statusCode = $response->getStatusCode();
            $data       = json_decode((string)$response->getBody(), true);

            if (!is_array($data)) {
                throw new DiscordException('Discord: Received invalid response.');
            }
            if ($statusCode === 429 && isset($data['retry_after'])) {
                if (++$tries > self::RETRY_LIMIT) {
                    throw new DiscordRateLimitException();
                }
                $this->logger->debug(
                    sprintf('Discord: Rate limited. Sleeping for %d.', $data['retry_after'])
                );
                usleep($data['retry_after'] * 1000);
                continue;
            } else if ($statusCode !== 200) {
                $this->logger->debug(
                    sprintf('Discord: Received status code %d.', $statusCode),
                    $data
                );
                throw new DiscordException($statusCode);
            }

            break;
        }

        if (!$cacheItem) {
            $cacheItem = new CacheItem();
        }
        $cacheItem->set($data)->expiresAfter(self::CACHE_TIME);
        $this->cache->save($cacheItem);

        return $data;
    }

    /**
     * @param mixed $body
     * @param mixed $token
     *
     * @return array
     */
    protected function buildRequestOptions($body, $token)
    {
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

        $options = [
            'headers'     => $headers,
            'http_errors' => false,
            'timeout'     => self::TIMEOUT
        ];
        if ($body !== null) {
            $options['body'] = json_encode($body);
        }

        return $options;
    }
}
