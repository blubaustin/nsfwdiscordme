<?php
namespace App\Event\Subscriber;

use App\Admin\LoggableEntityInterface;
use App\Entity\AdminEvent;
use App\Entity\User;
use App\Event\AdminLoginEvent;
use App\Event\AppEvents;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Event\EasyAdminEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class AdminLogsSubscriber
 */
class AdminLogsSubscriber implements EventSubscriberInterface
{
    /**
     * @var array
     */
    protected static $messageEvents = [
        AdminEvent::TYPE_NEW_ENTITY    => 'Created %s.',
        AdminEvent::TYPE_DELETE_ENTITY => 'Deleted %s.',
        AdminEvent::TYPE_UPDATE_ENTITY => 'Updated %s.',
        AdminEvent::TYPE_LOGIN         => 'Logged in %s.'
    ];

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var User
     */
    protected $user;

    /**
     * Constructor
     *
     * @param EntityManagerInterface $em
     * @param TokenStorageInterface  $tokenStorage
     */
    public function __construct(EntityManagerInterface $em, TokenStorageInterface $tokenStorage)
    {
        $this->em   = $em;
        $this->user = $tokenStorage->getToken()->getUser();
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            EasyAdminEvents::PRE_REMOVE   => 'preRemove',
            EasyAdminEvents::POST_PERSIST => 'postPersist',
            EasyAdminEvents::POST_UPDATE  => 'postUpdate',
            AppEvents::ADMIN_LOGIN        => 'adminLogin'
        ];
    }

    /**
     * @param AdminLoginEvent $event
     */
    public function adminLogin(AdminLoginEvent $event)
    {
        $this->logEntity($event, AdminEvent::TYPE_LOGIN);
    }

    /**
     * @param GenericEvent $event
     */
    public function preRemove(GenericEvent $event)
    {
        $entity = $event->getArgument('entity');
        if ($entity instanceof LoggableEntityInterface) {
            $this->logEntity($entity, AdminEvent::TYPE_DELETE_ENTITY);
        }
    }

    /**
     * @param GenericEvent $event
     */
    public function postPersist(GenericEvent $event)
    {
        $entity = $event->getArgument('entity');
        if ($entity instanceof LoggableEntityInterface) {
            $this->logEntity($entity, AdminEvent::TYPE_NEW_ENTITY);
        }
    }

    /**
     * @param GenericEvent $event
     */
    public function postUpdate(GenericEvent $event)
    {
        $entity = $event->getArgument('entity');
        if ($entity instanceof LoggableEntityInterface) {
            $this->logEntity($entity, AdminEvent::TYPE_UPDATE_ENTITY);
        }
    }

    /**
     * @param LoggableEntityInterface $entity
     * @param int                     $eventType
     */
    private function logEntity(LoggableEntityInterface $entity, $eventType)
    {
        $message = sprintf(self::$messageEvents[$eventType], $entity->getLoggableMessage());
        $adminEvent = (new AdminEvent())
            ->setUser($this->user)
            ->setEventType($eventType)
            ->setMessage($message);
        $this->em->persist($adminEvent);
        $this->em->flush();
    }
}
