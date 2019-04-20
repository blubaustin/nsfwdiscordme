<?php
namespace App\Controller;

use App\Entity\User;
use App\Storage\Snowflake\SnowflakeGeneratorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class Controller
 */
class Controller extends AbstractController
{
    const LIMIT = 20;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var SnowflakeGeneratorInterface
     */
    protected $snowflakeGenerator;

    /**
     * @var PaginatorInterface
     */
    private $paginator;

    /**
     * Constructor
     *
     * @param EntityManagerInterface      $em
     * @param EventDispatcherInterface    $eventDispatcher
     * @param SnowflakeGeneratorInterface $snowflakeGenerator
     * @param PaginatorInterface          $paginator
     */
    public function __construct(
        EntityManagerInterface $em,
        EventDispatcherInterface $eventDispatcher,
        SnowflakeGeneratorInterface $snowflakeGenerator,
        PaginatorInterface $paginator
    )
    {
        $this->em                 = $em;
        $this->eventDispatcher    = $eventDispatcher;
        $this->snowflakeGenerator = $snowflakeGenerator;
        $this->paginator          = $paginator;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return parent::getUser();
    }

    /**
     * @param mixed $query
     * @param int   $limit
     *
     * @return PaginationInterface
     */
    public function paginate($query, $limit = self::LIMIT)
    {
        return $this->paginator->paginate(
            $query,
            $this->get('request_stack')->getMasterRequest()->query->getInt('page', 1),
            $limit
        );
    }
}
