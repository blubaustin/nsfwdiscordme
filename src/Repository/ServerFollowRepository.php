<?php
namespace App\Repository;

use App\Entity\Server;
use App\Entity\ServerFollow;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class ServerFollowRepository
 */
class ServerFollowRepository extends ServiceEntityRepository
{
    /**
     * Constructor
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ServerFollow::class);
    }

    /**
     * @param $id
     *
     * @return object|ServerFollow
     */
    public function findByID($id)
    {
        return $this->findOneBy(['id' => $id]);
    }

    /**
     * @param Server $server
     * @param User   $user
     *
     * @return object|ServerFollow
     */
    public function findFollow(Server $server, User $user)
    {
        return $this->findOneBy([
            'user'   => $user,
            'server' => $server
        ]);
    }

    /**
     * @param Server $server
     * @param User   $user
     *
     * @return bool
     */
    public function isFollowing(Server $server, User $user)
    {
        $row = $this->findFollow($server, $user);

        return !empty($row);
    }
}
