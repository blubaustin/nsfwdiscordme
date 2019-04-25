<?php
namespace App\Repository;

use App\Entity\ServerTeamMember;
use App\Entity\Server;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class ServerTeamMemberRepository
 */
class ServerTeamMemberRepository extends ServiceEntityRepository
{
    /**
     * Constructor
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ServerTeamMember::class);
    }

    /**
     * @param int $id
     *
     * @return object|ServerTeamMember
     */
    public function findByID($id)
    {
        return $this->findOneBy(['id' => $id]);
    }

    /**
     * @param Server $server
     *
     * @return ServerTeamMember[]
     */
    public function findByServer(Server $server)
    {
        return $this->createQueryBuilder('t')
            ->where('t.server = :server')
            ->setParameter(':server', $server)
            ->orderBy('t.id', 'asc')
            ->getQuery()
            ->execute();
    }

    /**
     * @param User $user
     *
     * @return ServerTeamMember[]
     */
    public function findByUser(User $user)
    {
        return $this->findBy(['user' => $user]);
    }

    /**
     * @param Server $server
     * @param User   $user
     *
     * @return object|ServerTeamMember
     */
    public function findByServerAndUser(Server $server, User $user)
    {
        return $this->findOneBy([
            'server' => $server,
            'user'   => $user
        ]);
    }

    /**
     * @param string $discordUsername
     * @param int    $discordDiscriminator
     *
     * @return object|ServerTeamMember
     */
    public function findByDiscordUsernameAndDiscriminator($discordUsername, $discordDiscriminator)
    {
        return $this->findOneBy([
            'discordUsername'      => $discordUsername,
            'discordDiscriminator' => $discordDiscriminator
        ]);
    }
}
