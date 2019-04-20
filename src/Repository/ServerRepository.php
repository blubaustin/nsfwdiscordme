<?php
namespace App\Repository;

use App\Entity\Server;
use App\Entity\User;
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
     * @param int $id
     *
     * @return object|Server
     */
    public function findByID($id)
    {
        return $this->findOneBy(['id' => $id]);
    }

    /**
     * @param string $discordID
     *
     * @return object|Server
     */
    public function findByDiscordID($discordID)
    {
        return $this->findOneBy(['discordID' => $discordID]);
    }

    /**
     * @param string $slug
     *
     * @return object|Server
     */
    public function findBySlug($slug)
    {
        return $this->findOneBy(['slug' => $slug]);
    }

    /**
     * @param User $user
     *
     * @return Server[]
     */
    public function findByUser(User $user)
    {
        return $this->findBy(['user' => $user, 'isEnabled' => true]);
    }
}
