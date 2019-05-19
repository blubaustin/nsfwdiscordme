<?php
namespace App\Event;

use App\Admin\LoggableEntityInterface;
use App\Entity\User;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class AdminLoginEvent
 */
class AdminLoginEvent extends Event implements LoggableEntityInterface
{
    /**
     * @var User
     */
    protected $user;

    /**
     * Constructor
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getLoggableMessage()
    {
        return sprintf('at %s', date('Y-m-d H:i:s'));
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }
}
