<?php
namespace App\Repository;

use App\Entity\ServerEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
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

    /**
     * @param int $event
     *
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function findLastByEvent($event)
    {
        return $this->createQueryBuilder('e')
            ->where('e.eventType = :event')
            ->setParameter(':event', $event)
            ->setMaxResults(1)
            ->orderBy('e.id', 'desc')
            ->getQuery()
            ->getOneOrNullResult();
    }
}
