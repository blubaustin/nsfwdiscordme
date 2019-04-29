<?php
namespace App\Event\Listener;

use App\Entity\ServerEvent;
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
            $bse = (new ServerEvent())
                ->setServer($event->getServer())
                ->setEventType(ServerEvent::TYPE_BUMP)
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
            $jse = (new ServerEvent())
                ->setServer($event->getServer())
                ->setEventType(ServerEvent::TYPE_JOIN)
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
            $vse = (new ServerEvent())
                ->setServer($event->getServer())
                ->setEventType(ServerEvent::TYPE_VIEW)
                ->setIpString($event->getRequest()->getClientIp());
            $this->em->persist($vse);
            $this->em->flush();
        } catch (Exception $e) {}
    }
}
