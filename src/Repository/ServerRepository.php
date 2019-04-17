<?php
namespace App\Repository;

use App\Entity\Server;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class GuildRepository
 */
class ServerRepository extends ServiceEntityRepository
{
    /**
     * Constructor
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Server::class);
    }

    /**
     * @param $id
     *
     * @return object|Server
     */
    public function findByID($id)
    {
        return $this->findOneBy(['id' => $id]);
    }
}
