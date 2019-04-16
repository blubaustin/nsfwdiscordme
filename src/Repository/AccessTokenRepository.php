<?php
namespace App\Repository;

use App\Entity\AccessToken;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class AccessTokenRepository
 */
class AccessTokenRepository extends ServiceEntityRepository
{
    /**
     * Constructor
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, AccessToken::class);
    }

    /**
     * @param $id
     *
     * @return object|AccessToken
     */
    public function findByID($id)
    {
        return $this->findOneBy(['id' => $id]);
    }
}
