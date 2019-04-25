<?php
namespace App\Event\Listener;

use App\Entity\ServerBumpEvent;
use App\Entity\ServerJoinEvent;
use App\Entity\ServerViewEvent;
use App\Event\BumpEvent;
use App\Event\JoinEvent;
use App\Event\ViewEvent;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

/**
 * Class ServerStatsListener
 */
class ServerStatsListener
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
            $bse = (new ServerBumpEvent())
                ->setServer($event->getServer())
                ->setIpString($event->getRequest()->getClientIp());
            $this->em->persist($bse);
            $this->em->flush();
        } catch (Exception $e) {}
    }

    /**
     * @param JoinEvent $event
     */
    public function onJoin(JoinEvent $event)
    {
        try {
            $jse = (new ServerJoinEvent())
                ->setServer($event->getServer())
                ->setIpString($event->getRequest()->getClientIp());
            $this->em->persist($jse);
            $this->em->flush();
        } catch (Exception $e) {}
    }

    /**
     * @param ViewEvent $event
     */
    public function onView(ViewEvent $event)
    {
        try {
            $vse = (new ServerViewEvent())
                ->setServer($event->getServer())
                ->setIpString($event->getRequest()->getClientIp());
            $this->em->persist($vse);
            $this->em->flush();
        } catch (Exception $e) {}
    }
}
