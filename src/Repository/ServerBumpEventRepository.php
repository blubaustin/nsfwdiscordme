<?php
namespace App\Repository;

use App\Entity\ServerBumpEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class ServerBumpEventRepository
 */
class ServerBumpEventRepository extends ServiceEntityRepository
{
    /**
     * Constructor
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ServerBumpEvent::class);
    }

    /**
     * @param int $id
     *
     * @return object|ServerBumpEvent
     */
    public function findByID($id)
    {
        return $this->findOneBy(['id' => $id]);
    }
}
