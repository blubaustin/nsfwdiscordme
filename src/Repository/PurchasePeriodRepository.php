<?php
namespace App\Repository;

use App\Entity\PurchasePeriod;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Exception;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class PurchasePeriodRepository
 */
class PurchasePeriodRepository extends ServiceEntityRepository
{
    /**
     * Constructor
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PurchasePeriod::class);
    }

    /**
     * @param int $id
     *
     * @return object|PurchasePeriod
     */
    public function findByID($id)
    {
        return $this->findOneBy(['id' => $id]);
    }

    /**
     * @return PurchasePeriod[]
     * @throws Exception
     */
    public function findExpired()
    {
        return $this->createQueryBuilder('p')
            ->where('p.isComplete = 0')
            ->andWhere('p.dateExpires <= :now')
            ->setParameter(':now', new DateTime())
            ->getQuery()
            ->execute();
    }

    /**
     * @return PurchasePeriod[]
     * @throws Exception
     */
    public function findReady()
    {
        return $this->createQueryBuilder('p')
            ->where('p.isComplete = 0')
            ->andWhere('p.dateBegins IS NULL')
            ->getQuery()
            ->execute();
    }
}
