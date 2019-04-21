<?php
namespace App\Repository;

use App\Entity\BumpPeriodVote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
}
