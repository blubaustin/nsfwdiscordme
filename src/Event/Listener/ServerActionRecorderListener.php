<?php
namespace App\Event\Listener;

use App\Entity\ServerTeamMember;
use App\Event\ServerActionEvent;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

/**
 * Class ServerActionRecorderListener
 */
class ServerActionRecorderListener
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
     * @param ServerActionEvent $event
     */
    public function onAction(ServerActionEvent $event)
    {
        try {
            $user       = $event->getUser();
            $server     = $event->getServer();
            $teamMember = $this->em->getRepository(ServerTeamMember::class)
                ->findByServerAndUser($server, $user);
            if ($teamMember) {
                $teamMember->setDateLastAction(new DateTime());
                $this->em->flush();
            }
        } catch (Exception $e) {}
    }
}
