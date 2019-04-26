<?php
namespace App\Security;

use App\Entity\Server;
use App\Entity\ServerTeamMember;
use App\Entity\User;
use App\Repository\ServerTeamMemberRepository;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Provides a method indicating what access a user has to a server.
 */
class ServerAccess implements ServerAccessInterface
{
    const ROLE_OWNER   = 'owner';
    const ROLE_MANAGER = 'manager';
    const ROLE_EDITOR  = 'editor';
    const ROLE_NONE    = 'none';

    const ROLES = [
        self::ROLE_OWNER => [
            self::ROLE_OWNER,
            self::ROLE_MANAGER,
            self::ROLE_EDITOR
        ],
        self::ROLE_MANAGER => [
            self::ROLE_MANAGER,
            self::ROLE_EDITOR
        ],
        self::ROLE_EDITOR => [
            self::ROLE_EDITOR
        ]
    ];

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var ServerTeamMemberRepository
     */
    protected $repo;

    /**
     * Constructor
     *
     * @param TokenStorageInterface  $tokenStorage
     * @param EntityManagerInterface $em
     */
    public function __construct(TokenStorageInterface $tokenStorage, EntityManagerInterface $em)
    {
        $this->tokenStorage = $tokenStorage;
        $this->repo         = $em->getRepository(ServerTeamMember::class);
    }

    /**
     * {@inheritDoc}
     */
    public function getRoleNames()
    {
        return [
            self::ROLE_OWNER,
            self::ROLE_MANAGER,
            self::ROLE_EDITOR,
            self::ROLE_NONE
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function can(Server $server, $role, User $user = null)
    {
        if (!in_array($role, $this->getRoleNames())) {
            throw new InvalidArgumentException(
                "Server role ${role} is invalid."
            );
        }

        /** @var User $user */
        if (!$user) {
            $token = $this->tokenStorage->getToken();
            if (!$token) {
                return false;
            }
            $user = $token->getUser();
        }
        if (!$user || !is_object($user)) {
            return false;
        }

        $teamMember = $this->repo->findByServerAndUser($server, $user);
        if (!$teamMember) {
            return false;
        }

        return $this->containsRole($role, $teamMember->getRole());
    }

    /**
     * {@inheritDoc}
     */
    public function containsRole($isRole, $canRole)
    {
        return in_array($isRole, self::ROLES[$canRole]);
    }
}
