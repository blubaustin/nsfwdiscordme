<?php
namespace App\Twig;

use App\Entity\Server;
use Exception;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Twig\Extension\AbstractExtension;
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
     * Constructor
     *
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @return TwigFilter[]
     */
    public function getFilters()
    {
        return [
            new TwigFilter('serverURL', [$this, 'serverURL']),
            new TwigFilter('serverNextBump', [$this, 'serverNextBump'])
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

        return $interval->format("%ad %hh %im %ss");
    }
}
