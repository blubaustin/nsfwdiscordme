<?php
namespace App\Controller;

use App\Entity\User;
use App\Storage\Snowflake\SnowflakeGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class Controller
 */
class Controller extends AbstractController
{
    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var SnowflakeGeneratorInterface
     */
    protected $snowflakeGenerator;

    /**
     * Constructor
     *
     * @param EventDispatcherInterface    $eventDispatcher
     * @param SnowflakeGeneratorInterface $snowflakeGenerator
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, SnowflakeGeneratorInterface $snowflakeGenerator)
    {
        $this->eventDispatcher    = $eventDispatcher;
        $this->snowflakeGenerator = $snowflakeGenerator;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return parent::getUser();
    }
}
