<?php
namespace App\Form\Model;

/**
 * Class ServerTeamMemberModel
 */
class ServerTeamMemberModel
{
    /**
     * @var string
     */
    protected $username = '';

    /**
     * @var string
     */
    protected $role = '';

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     *
     * @return ServerTeamMemberModel
     */
    public function setUsername(string $username): ServerTeamMemberModel
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return string
     */
    public function getRole(): string
    {
        return $this->role;
    }

    /**
     * @param string $role
     *
     * @return ServerTeamMemberModel
     */
    public function setRole(string $role): ServerTeamMemberModel
    {
        $this->role = $role;

        return $this;
    }
}
