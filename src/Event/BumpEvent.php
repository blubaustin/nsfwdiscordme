<?php
namespace App\Event;

use App\Entity\Server;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;

/**
 * Triggered when a server is bumped.
 */
class BumpEvent extends Event
{
    /**
     * @var Server
     */
    protected $server;

    /**
     * @var Request
     */
    protected $request;

    /**
     * Constructor
     *
     * @param Server  $server
     * @param Request $request
     */
    public function __construct(Server $server, Request $request)
    {
        $this->server  = $server;
        $this->request = $request;
    }

    /**
     * @return Server
     */
    public function getServer(): Server
    {
        return $this->server;
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }
}
