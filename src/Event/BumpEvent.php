<?php
namespace App\Event;

use App\Entity\Server;
use App\Entity\User;
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
     * @var User
     */
    protected $user;

    /**
     * @var Request
     */
    protected $request;

    /**
     * Constructor
     *
     * @param Server  $server
     * @param User    $user
     * @param Request $request
     */
    public function __construct(Server $server, User $user, Request $request)
    {
        $this->server  = $server;
        $this->user    = $user;
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
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }
}
