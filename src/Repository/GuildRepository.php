<?php
namespace App\Repository;

use App\Entity\Guild;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class GuildRepository
 */
class GuildRepository extends ServiceEntityRepository
{
    /**
     * Constructor
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Guild::class);
    }

    /**
     * @param $id
     *
     * @return object|Guild
     */
    public function findByID($id)
    {
        return $this->findOneBy(['id' => $id]);
    }
}
