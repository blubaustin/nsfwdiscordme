<?php
namespace App\Repository;

use App\Entity\Category;
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

    /**
     * @param int $limit
     * @param int $offset
     *
     * @return Server[]
     */
    public function findByRecent($limit = 20, $offset = 0)
    {
        return $this->createQueryBuilder('s')
            ->where('s.isEnabled = 1')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->execute();
    }

    /**
     * @param Category $category
     * @param int      $limit
     * @param int      $offset
     *
     * @return Server[]
     */
    public function findByCategory(Category $category, $limit = 20, $offset = 0)
    {
        return $this->createQueryBuilder('s')
            ->select('s')
            ->leftJoin('s.categories', 'category')
            ->where('s.isEnabled = 1')
            ->andWhere('category = :category')
            ->setParameter(':category', $category)
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->execute();
    }
}
