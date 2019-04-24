<?php
namespace App\Repository;

use App\Entity\BumpPeriodVote;
use App\Entity\Server;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class BumpPeriodVoteRepository
 */
class BumpPeriodVoteRepository extends ServiceEntityRepository
{
    /**
     * Constructor
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, BumpPeriodVote::class);
    }

    /**
     * @param int $id
     *
     * @return object|BumpPeriodVote
     */
    public function findByID($id)
    {
        return $this->findOneBy(['id' => $id]);
    }

    /**
     * @param Server $server
     *
     * @return BumpPeriodVote
     * @throws NonUniqueResultException
     */
    public function findLastBump(Server $server)
    {
        return $this->createQueryBuilder('b')
            ->where('b.server = :server')
            ->setParameter(':server', $server)
            ->orderBy('b.id', 'desc')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
