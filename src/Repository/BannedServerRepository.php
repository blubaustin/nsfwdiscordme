<?php
namespace App\Repository;

use App\Entity\BannedServer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class BannedServerRepository
 */
class BannedServerRepository extends ServiceEntityRepository
{
    /**
     * Constructor
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, BannedServer::class);
    }

    /**
     * @param int $id
     *
     * @return object|BannedServer
     */
    public function findByID($id)
    {
        return $this->findOneBy(['id' => $id]);
    }

    /**
     * @param string|int $discordID
     *
     * @return bool
     */
    public function isBanned($discordID)
    {
        $row = $this->findOneBy(['discordID' => $discordID]);

        return !empty($row);
    }
}
