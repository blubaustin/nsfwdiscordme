<?php
namespace App\Repository;

use App\Entity\ServerEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class ServerEventRepository
 */
class ServerEventRepository extends ServiceEntityRepository
{
    /**
     * Constructor
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ServerEvent::class);
    }

    /**
     * @param int $id
     *
     * @return object|ServerEvent
     */
    public function findByID($id)
    {
        return $this->findOneBy(['id' => $id]);
    }
}
