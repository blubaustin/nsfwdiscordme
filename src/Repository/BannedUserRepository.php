<?php
namespace App\Repository;

use App\Entity\BannedUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class BannedUserRepository
 */
class BannedUserRepository extends ServiceEntityRepository
{
    /**
     * Constructor
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, BannedUser::class);
    }

    /**
     * @param int $id
     *
     * @return object|BannedUser
     */
    public function findByID($id)
    {
        return $this->findOneBy(['id' => $id]);
    }

    /**
     * @param string $discordUsername
     * @param string $discordDiscriminator
     *
     * @return bool
     */
    public function isBanned($discordUsername, $discordDiscriminator)
    {
        $row = $this->findOneBy([
            'discordUsername'      => $discordUsername,
            'discordDiscriminator' => $discordDiscriminator
        ]);

        return !empty($row);
    }
}
