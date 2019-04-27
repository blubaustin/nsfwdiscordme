<?php
namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
     * @var Collection
     * @ORM\OneToMany(targetEntity="Server", mappedBy="user")
     */
    protected $servers;

    /**
     * @var DateTime
     * @ORM\Column(type="datetime")
     */
    protected $dateCreated;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->dateCreated = new DateTime();
        $this->servers     = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getDiscordUsername() . '#' . $this->getDiscordDiscriminator();
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

    /**
     * @return Collection|Server[]
     */
    public function getServers(): Collection
    {
        return $this->servers;
    }

    /**
     * @param Collection $servers
     *
     * @return User
     */
    public function setServers(Collection $servers): User
    {
        $this->servers = $servers;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDateCreated(): DateTime
    {
        return $this->dateCreated;
    }

    /**
     * @param DateTime $dateCreated
     *
     * @return User
     */
    public function setDateCreated(DateTime $dateCreated): User
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }
}
