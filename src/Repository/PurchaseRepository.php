<?php
namespace App\Repository;

use App\Entity\Purchase;
use App\Entity\User;
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
     * @param User $user
     *
     * @return Purchase[]
     */
    public function findByUser(User $user)
    {
        return $this->findBy([
            'user' => $user,
            'status' => Purchase::STATUS_SUCCESS
        ]);
    }
}
