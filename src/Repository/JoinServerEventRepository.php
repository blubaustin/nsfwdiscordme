<?php
namespace App\Repository;

use App\Entity\JoinServerEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class JoinServerEventRepository
 */
class JoinServerEventRepository extends ServiceEntityRepository
{
    /**
     * Constructor
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, JoinServerEvent::class);
    }

    /**
     * @param int $id
     *
     * @return object|JoinServerEvent
     */
    public function findByID($id)
    {
        return $this->findOneBy(['id' => $id]);
    }
}
