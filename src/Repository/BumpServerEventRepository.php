<?php
namespace App\Repository;

use App\Entity\BumpServerEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class BumpServerEventRepository
 */
class BumpServerEventRepository extends ServiceEntityRepository
{
    /**
     * Constructor
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, BumpServerEvent::class);
    }

    /**
     * @param int $id
     *
     * @return object|BumpServerEvent
     */
    public function findByID($id)
    {
        return $this->findOneBy(['id' => $id]);
    }
}
