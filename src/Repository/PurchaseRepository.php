<?php
namespace App\Repository;

use App\Entity\Purchase;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class PurchaseRepository
 */
class PurchaseRepository extends ServiceEntityRepository
{
    /**
     * Constructor
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Purchase::class);
    }

    /**
     * @param int $id
     *
     * @return object|Purchase
     */
    public function findByID($id)
    {
        return $this->findOneBy(['id' => $id]);
    }

    /**
     * @param string $purchaseToken
     *
     * @return object|Purchase
     */
    public function findByPurchaseToken($purchaseToken)
    {
        return $this->findOneBy(['purchaseToken' => $purchaseToken]);
    }
}
