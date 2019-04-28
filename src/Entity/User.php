<?php
namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User implements UserInterface
{
    const ROLE_DEFAULT     = 'ROLE_USER';
    const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned"=true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var array
     * @ORM\Column(type="array")
     */
    protected $roles;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $enabled;

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
     * @var DateTime
     * @ORM\Column(type="datetime")
     */
    protected $dateLastLogin;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->dateCreated   = new DateTime();
        $this->dateLastLogin = new DateTime();
        $this->servers       = new ArrayCollection();
        $this->roles         = [];
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getUsername();
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     *
     * @return User
     */
    public function setEnabled(bool $enabled): User
    {
        $this->enabled = $enabled;

        return $this;
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

    /**
     * @return DateTime
     */
    public function getDateLastLogin(): DateTime
    {
        return $this->dateLastLogin;
    }

    /**
     * @param DateTime $dateLastLogin
     *
     * @return User
     */
    public function setDateLastLogin(DateTime $dateLastLogin): User
    {
        $this->dateLastLogin = $dateLastLogin;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @param string $role
     *
     * @return bool
     */
    public function hasRole($role): bool
    {
        return in_array(strtoupper($role), $this->getRoles(), true);
    }

    /**
     * @param array $roles
     *
     * @return User
     */
    public function setRoles(array $roles): User
    {
        $this->roles = array();

        foreach ($roles as $role) {
            $this->addRole($role);
        }

        return $this;
    }

    /**
     * @param string $role
     *
     * @return User
     */
    public function addRole($role): User
    {
        $role = strtoupper($role);
        if ($role === self::ROLE_DEFAULT) {
            return $this;
        }

        if (!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return '';
    }

    /**
     * @return string|null
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->getDiscordUsername();
    }

    /**
     * {@inheritDoc}
     */
    public function eraseCredentials()
    {
        // Nothing here
    }
}
