<?php
namespace App\Event;

use App\Entity\Server;
use App\Entity\User;
use Symfony\Component\EventDispatcher\Event;

/**
 * Triggered when an action is performed on a server by a team member.
 */
class ServerActionEvent extends Event
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
     * @var string
     */
    protected $action;

    /**
     * Constructor
     *
     * @param Server $server
     * @param User   $user
     * @param string $action
     */
    public function __construct(Server $server, ?User $user, $action)
    {
        $this->server = $server;
        $this->user   = $user;
        $this->action = $action;
    }

    /**
     * @return Server
     */
    public function getServer(): Server
    {
        return $this->server;
    }

    /**
     * @param Server $server
     *
     * @return ServerActionEvent
     */
    public function setServer(Server $server): ServerActionEvent
    {
        $this->server = $server;

        return $this;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     *
     * @return ServerActionEvent
     */
    public function setUser(User $user): ServerActionEvent
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @param string $action
     *
     * @return ServerActionEvent
     */
    public function setAction(string $action): ServerActionEvent
    {
        $this->action = $action;

        return $this;
    }
}
