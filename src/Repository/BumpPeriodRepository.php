<?php
namespace App\Repository;

use App\Entity\BumpPeriod;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bridge\Doctrine\RegistryInterface;
use DateInterval;
use DateTime;

/**
 * Class BumpPeriodRepository
 */
class BumpPeriodRepository extends ServiceEntityRepository
{
    /**
     * Constructor
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, BumpPeriod::class);
    }

    /**
     * @param int $id
     *
     * @return object|BumpPeriod
     */
    public function findByID($id)
    {
        return $this->findOneBy(['id' => $id]);
    }

    /**
     * @return BumpPeriod
     * @throws NonUniqueResultException
     */
    public function findCurrentPeriod()
    {
        $now  = new DateTime();
        $hour = (int)date('H');
        if ($hour >= 0 && $hour < 6) {
            $then = clone $now->setTime(0, 0, 0);
        } else if ($hour >= 6 && $hour < 12) {
            $then = clone $now->setTime(6, 0, 0);
        } else if ($hour >= 12 && $hour < 18) {
            $then = clone $now->setTime(12, 0, 0);
        } else {
            $then = clone $now->setTime(18, 0, 0);
        }
        $then->add(new DateInterval('PT5H59M'));

        return $this->createQueryBuilder('b')
            ->where('b.date BETWEEN :now AND :then')
            ->setParameter(':now', $now)
            ->setParameter(':then', $then)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
