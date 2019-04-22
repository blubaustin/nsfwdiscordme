<?php
namespace App\Event\Listener;

use App\Entity\JoinServerEvent;
use App\Event\JoinEvent;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

/**
 * Records server joins, the data will be used to generate join stats.
 */
class JoinStatsListener
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * Constructor
     *
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param JoinEvent $event
     */
    public function onJoin(JoinEvent $event)
    {
        try {
            $bse = (new JoinServerEvent())
                ->setServer($event->getServer())
                ->setIpString($event->getRequest()->getClientIp());
            $this->em->persist($bse);
            $this->em->flush();
        } catch (Exception $e) {}
    }
}
