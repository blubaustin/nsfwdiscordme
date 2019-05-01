<?php
namespace App\Repository;

use App\Entity\ServerAction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class ServerActionRepository
 */
class ServerActionRepository extends ServiceEntityRepository
{
    /**
     * Constructor
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ServerAction::class);
    }

    /**
     * @param $id
     *
     * @return object|ServerAction
     */
    public function findByID($id)
    {
        return $this->findOneBy(['id' => $id]);
    }
}
