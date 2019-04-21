<?php
namespace App\Event\Listener;

use App\Entity\BumpServerEvent;
use App\Event\BumpEvent;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

/**
 * Records server bumps, the data will be used to generate bump stats.
 */
class BumpStatsListener
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
     * @param BumpEvent $event
     */
    public function onBump(BumpEvent $event)
    {
        try {
            $bse = (new BumpServerEvent())
                ->setServer($event->getServer())
                ->setIp(inet_pton($event->getRequest()->getClientIp()));
            $this->em->persist($bse);
            $this->em->flush();
        } catch (Exception $e) {}
    }
}
