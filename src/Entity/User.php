<?php
namespace App\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned"=true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var AccessToken
     * @ORM\OneToOne(targetEntity="AccessToken", mappedBy="user")
     */
    protected $discordAccessToken;

    /**
     * @var int
     * @ORM\Column(type="bigint", options={"unsigned"=true}, nullable=true)
     */
    protected $discordID;

    /**
     * @var string
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    protected $discordUsername;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $discordEmail;

    /**
     * @var string
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    protected $discordAvatar;

    /**
     * @var string
     * @ORM\Column(type="string", length=4, nullable=true)
     */
    protected $discordDiscriminator;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        // your own logic
    }

    /**
     * @return AccessToken
     */
    public function getDiscordAccessToken(): ?AccessToken
    {
        return $this->discordAccessToken;
    }

    /**
     * @param AccessToken $discordAccessToken
     *
     * @return User
     */
    public function setDiscordAccessToken(AccessToken $discordAccessToken): User
    {
        $this->discordAccessToken = $discordAccessToken;

        return $this;
    }

    /**
     * @return int
     */
    public function getDiscordID(): int
    {
        return $this->discordID;
    }

    /**
     * @param int $discordID
     *
     * @return User
     */
    public function setDiscordID(int $discordID): User
    {
        $this->discordID = $discordID;

        return $this;
    }

    /**
     * @return string
     */
    public function getDiscordUsername(): string
    {
        return $this->discordUsername;
    }

    /**
     * @param string $discordUsername
     *
     * @return User
     */
    public function setDiscordUsername(string $discordUsername): User
    {
        $this->discordUsername = $discordUsername;

        return $this;
    }

    /**
     * @return string
     */
    public function getDiscordEmail(): string
    {
        return $this->discordEmail;
    }

    /**
     * @param string $discordEmail
     *
     * @return User
     */
    public function setDiscordEmail(string $discordEmail): User
    {
        $this->discordEmail = $discordEmail;

        return $this;
    }

    /**
     * @return string
     */
    public function getDiscordAvatar(): string
    {
        return $this->discordAvatar;
    }

    /**
     * @param string $discordAvatar
     *
     * @return User
     */
    public function setDiscordAvatar(string $discordAvatar): User
    {
        $this->discordAvatar = $discordAvatar;

        return $this;
    }

    /**
     * @return string
     */
    public function getDiscordDiscriminator(): string
    {
        return $this->discordDiscriminator;
    }

    /**
     * @param string $discordDiscriminator
     *
     * @return User
     */
    public function setDiscordDiscriminator(string $discordDiscriminator): User
    {
        $this->discordDiscriminator = $discordDiscriminator;

        return $this;
    }
}
