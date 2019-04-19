<?php
namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use DateTime;
use Exception;

/**
 * @ORM\Table(name="server")
 * @ORM\Entity(repositoryClass="App\Repository\ServerRepository")
 */
class Server
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
     * @ORM\ManyToOne(targetEntity="User", inversedBy="servers")
     * @ORM\JoinColumn(name="user_id", onDelete="CASCADE", referencedColumnName="id")
     */
    protected $user;

    /**
     * @var string
     * @ORM\Column(type="string", length=100, unique=true)
     */
    protected $slug;

    /**
     * @var string
     * @ORM\Column(type="string", length=100)
     */
    protected $name;

    /**
     * @var string
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    protected $iconHash;

    /**
     * @var string
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    protected $bannerHash;

    /**
     * @var Media
     * @ORM\OneToOne(targetEntity="Media", cascade={"persist"})
     */
    protected $iconMedia;

    /**
     * @var Media
     * @ORM\OneToOne(targetEntity="Media", cascade={"persist"})
     */
    protected $bannerMedia;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $vanityURL;

    /**
     * @var string
     * @ORM\Column(type="string", length=160, nullable=true)
     */
    protected $summary;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @var int
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
    protected $bumpPoints = 0;

    /**
     * @var Collection
     * @ORM\ManyToMany(targetEntity="Category")
     * @ORM\JoinTable(
     *     name="server_categories",
     *     joinColumns={@ORM\JoinColumn(name="server_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="category_id", referencedColumnName="id")}
     * )
     */
    protected $categories;

    /**
     * @var int
     * @ORM\Column(type="bigint", options={"unsigned"=true}, nullable=true)
     */
    protected $botInviteChannelID;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $botHumanCheck = false;

    /**
     * @var string
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    protected $serverPassword;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $isPublic = true;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $isActive = true;

    /**
     * @var DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $dateNextBump;

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
        $this->categories  = new ArrayCollection();
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
    public function getDiscordID(): ?int
    {
        return $this->discordID;
    }

    /**
     * @param int $discordID
     *
     * @return Server
     */
    public function setDiscordID(int $discordID): Server
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
     * @return Server
     */
    public function setUser(User $user): Server
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return string
     */
    public function getSlug(): ?string
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     *
     * @return Server
     */
    public function setSlug(string $slug): Server
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return Server
     */
    public function setName(string $name): Server
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getIconHash(): string
    {
        return $this->iconHash;
    }

    /**
     * @param string $iconHash
     *
     * @return Server
     */
    public function setIconHash(string $iconHash): Server
    {
        $this->iconHash = $iconHash;

        return $this;
    }

    /**
     * @return string
     */
    public function getBannerHash(): string
    {
        return $this->bannerHash;
    }

    /**
     * @param string $bannerHash
     *
     * @return Server
     */
    public function setBannerHash(string $bannerHash): Server
    {
        $this->bannerHash = $bannerHash;

        return $this;
    }

    /**
     * @return Media
     */
    public function getIconMedia(): Media
    {
        return $this->iconMedia;
    }

    /**
     * @param Media $iconMedia
     *
     * @return Server
     */
    public function setIconMedia(Media $iconMedia): Server
    {
        $this->iconMedia = $iconMedia;

        return $this;
    }

    /**
     * @return Media
     */
    public function getBannerMedia(): Media
    {
        return $this->bannerMedia;
    }

    /**
     * @param Media $bannerMedia
     *
     * @return Server
     */
    public function setBannerMedia(Media $bannerMedia): Server
    {
        $this->bannerMedia = $bannerMedia;

        return $this;
    }

    /**
     * @return string
     */
    public function getVanityURL(): ?string
    {
        return $this->vanityURL;
    }

    /**
     * @param string $vanityURL
     *
     * @return Server
     */
    public function setVanityURL(string $vanityURL): Server
    {
        $this->vanityURL = $vanityURL;

        return $this;
    }

    /**
     * @return string
     */
    public function getSummary(): ?string
    {
        return $this->summary;
    }

    /**
     * @param string $summary
     *
     * @return Server
     */
    public function setSummary(string $summary): Server
    {
        $this->summary = $summary;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return Server
     */
    public function setDescription(string $description): Server
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection|Category[]
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    /**
     * @param Collection $categories
     *
     * @return Server
     */
    public function setCategories(Collection $categories): Server
    {
        $this->categories = $categories;

        return $this;
    }

    /**
     * @return int
     */
    public function getBumpPoints(): int
    {
        return $this->bumpPoints;
    }

    /**
     * @param int $bumpPoints
     *
     * @return Server
     */
    public function setBumpPoints(int $bumpPoints): Server
    {
        $this->bumpPoints = $bumpPoints;

        return $this;
    }

    /**
     * @return int
     */
    public function getBotInviteChannelID(): ?int
    {
        return $this->botInviteChannelID;
    }

    /**
     * @param int $botInviteChannelID
     *
     * @return Server
     */
    public function setBotInviteChannelID(int $botInviteChannelID): Server
    {
        $this->botInviteChannelID = $botInviteChannelID;

        return $this;
    }

    /**
     * @return bool
     */
    public function isBotHumanCheck(): bool
    {
        return $this->botHumanCheck;
    }

    /**
     * @param bool $botHumanCheck
     *
     * @return Server
     */
    public function setBotHumanCheck(bool $botHumanCheck): Server
    {
        $this->botHumanCheck = $botHumanCheck;

        return $this;
    }

    /**
     * @return string
     */
    public function getServerPassword(): ?string
    {
        return $this->serverPassword;
    }

    /**
     * @param string $serverPassword
     *
     * @return Server
     */
    public function setServerPassword(string $serverPassword): Server
    {
        $this->serverPassword = $serverPassword;

        return $this;
    }

    /**
     * @return bool
     */
    public function isPublic(): bool
    {
        return $this->isPublic;
    }

    /**
     * @param bool $isPublic
     *
     * @return Server
     */
    public function setIsPublic(bool $isPublic): Server
    {
        $this->isPublic = $isPublic;

        return $this;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @param bool $isActive
     *
     * @return Server
     */
    public function setIsActive(bool $isActive): Server
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDateNextBump(): ?DateTime
    {
        return $this->dateNextBump;
    }

    /**
     * @param DateTime $dateNextBump
     *
     * @return Server
     */
    public function setDateNextBump(DateTime $dateNextBump): Server
    {
        $this->dateNextBump = $dateNextBump;

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
     * @return Server
     */
    public function setDateCreated(DateTime $dateCreated): Server
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
     * @return Server
     */
    public function setDateUpdated(DateTime $dateUpdated): Server
    {
        $this->dateUpdated = $dateUpdated;

        return $this;
    }
}
