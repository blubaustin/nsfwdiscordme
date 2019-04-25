<?php
namespace App\Security;

use App\Entity\Server;
use App\Entity\User;
use InvalidArgumentException;

/**
 * Provides a method indicating what access a user has to a server.
 */
interface ServerAccessInterface
{
    /**
     * @return array
     */
    public function getRoleNames();

    /**
     * Returns a boolean indicating whether the authenticated user can perform a role on the given server
     *
     * Uses the authenticated user unless the $user argument is provided.
     *
     * @param Server $server
     * @param string $role
     * @param User   $user
     *
     * @return bool
     * @throws InvalidArgumentException
     */
    public function can(Server $server, $role, User $user = null);

    /**
     * @param string $isRole
     * @param string $canRole
     *
     * @return bool
     */
    public function containsRole($isRole, $canRole);
}
