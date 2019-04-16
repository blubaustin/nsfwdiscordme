<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use DateTime;
use Exception;

/**
 * @ORM\Table(name="guild")
 * @ORM\Entity(repositoryClass="App\Repository\GuildRepository")
 */
class Guild
{
    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", options={"unsigned"=true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var int
     * @ORM\Column(type="bigint", options={"unsigned"=true})
     */
    protected $discordID;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="User", inversedBy="guilds")
     * @ORM\JoinColumn(name="user_id", onDelete="CASCADE", referencedColumnName="id")
     */
    protected $user;

    /**
     * @var string
     * @ORM\Column(type="string", length=100)
     */
    protected $name;

    /**
     * @var string
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    protected $icon;

    /**
     * @var string
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    protected $banner;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $vanityURL;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @var DateTime
     * @ORM\Column(type="datetime")
     */
    protected $dateCreated;

    /**
     * @var DateTime
     * @ORM\Column(type="datetime")
     */
    protected $dateUpdated;

    /**
     * Constructor
     *
     * @throws Exception
     */
    public function __construct()
    {
        $this->dateCreated = new DateTime();
        $this->dateUpdated = new DateTime();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
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
     * @return Guild
     */
    public function setDiscordID(int $discordID): Guild
    {
        $this->discordID = $discordID;

        return $this;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     *
     * @return Guild
     */
    public function setUser(User $user): Guild
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return Guild
     */
    public function setName(string $name): Guild
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return $this->icon;
    }

    /**
     * @param string $icon
     *
     * @return Guild
     */
    public function setIcon(string $icon): Guild
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * @return string
     */
    public function getBanner(): string
    {
        return $this->banner;
    }

    /**
     * @param string $banner
     *
     * @return Guild
     */
    public function setBanner(string $banner): Guild
    {
        $this->banner = $banner;

        return $this;
    }

    /**
     * @return string
     */
    public function getVanityURL(): string
    {
        return $this->vanityURL;
    }

    /**
     * @param string $vanityURL
     *
     * @return Guild
     */
    public function setVanityURL(string $vanityURL): Guild
    {
        $this->vanityURL = $vanityURL;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return Guild
     */
    public function setDescription(string $description): Guild
    {
        $this->description = $description;

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
     * @return Guild
     */
    public function setDateCreated(DateTime $dateCreated): Guild
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDateUpdated(): DateTime
    {
        return $this->dateUpdated;
    }

    /**
     * @param DateTime $dateUpdated
     *
     * @return Guild
     */
    public function setDateUpdated(DateTime $dateUpdated): Guild
    {
        $this->dateUpdated = $dateUpdated;

        return $this;
    }
}
