<?php
namespace App\Twig;

use App\Entity\Server;
use App\Entity\User;
use App\Security\ServerAccessInterface;
use Exception;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig\TwigFilter;
use DateTime;

/**
 * Class ServerExtension
 */
class ServerExtension extends AbstractExtension
{
    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var ServerAccessInterface
     */
    protected $serverAccess;

    /**
     * Constructor
     *
     * @param RouterInterface       $router
     * @param ServerAccessInterface $serverAccess
     */
    public function __construct(RouterInterface $router, ServerAccessInterface $serverAccess)
    {
        $this->router       = $router;
        $this->serverAccess = $serverAccess;
    }

    /**
     * @return TwigFilter[]
     */
    public function getFilters()
    {
        return [
            new TwigFilter('serverURL', [$this, 'serverURL']),
            new TwigFilter('serverNextBump', [$this, 'serverNextBump']),
            new TwigFilter('premiumStatusString', [$this, 'premiumStatusString'])
        ];
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('hasServerAccess', [$this, 'hasServerAccess'])
        ];
    }

    /**
     * @param Server $server
     * @param int    $referenceType
     *
     * @return string
     */
    public function serverURL(Server $server, $referenceType = UrlGeneratorInterface::ABSOLUTE_URL)
    {
        return $this->router->generate('server_index', ['slug' => $server->getSlug()], $referenceType);
    }

    /**
     * @param Server $server
     *
     * @return string
     * @throws Exception
     */
    public function serverNextBump(Server $server)
    {
        $dateNextBump = $server->getDateNextBump();
        if (!$dateNextBump) {
            return '0';
        }

        $interval = $dateNextBump->diff(new DateTime());
        if ($interval->d !== 0) {
            return $interval->format("%ad %hh %im %ss");
        } else if ($interval->h !== 0) {
            return $interval->format("%hh %im %ss");
        }

        return $interval->format("%im %ss");
    }

    /**
     * @param Server $server
     * @param string $role
     * @param User   $user
     *
     * @return bool
     */
    public function hasServerAccess(Server $server, $role, User $user = null)
    {
        return $this->serverAccess->can($server, $role, $user);
    }

    /**
     * @param string $status
     *
     * @return string
     */
    public function premiumStatusString($status)
    {
        return Server::STATUSES_STR[$status];
    }
}
