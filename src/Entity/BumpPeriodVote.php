<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use DateTime;
use Exception;

/**
 * @ORM\Table(name="bump_period_vote",
 *     uniqueConstraints={
 *          @ORM\UniqueConstraint(columns={"user_id", "bump_period_id", "server_id"})
 *     }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\BumpPeriodVoteRepository")
 */
class BumpPeriodVote
{
    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", options={"unsigned"=true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", onDelete="CASCADE", referencedColumnName="id", nullable=false)
     */
    protected $user;

    /**
     * @var BumpPeriod
     * @ORM\ManyToOne(targetEntity="BumpPeriod")
     * @ORM\JoinColumn(name="bump_period_id", onDelete="CASCADE", referencedColumnName="id", nullable=false)
     */
    protected $bumpPeriod;

    /**
     * @var Server
     * @ORM\ManyToOne(targetEntity="Server")
     * @ORM\JoinColumn(name="server_id", onDelete="CASCADE", referencedColumnName="id", nullable=false)
     */
    protected $server;

    /**
     * @var DateTime
     * @ORM\Column(type="datetime")
     */
    protected $dateCreated;

    /**
     * Constructor
     *
     * @throws Exception
     */
    public function __construct()
    {
        $this->dateCreated = new DateTime();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
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
     * @return BumpPeriodVote
     */
    public function setUser(User $user): BumpPeriodVote
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return BumpPeriod
     */
    public function getBumpPeriod(): BumpPeriod
    {
        return $this->bumpPeriod;
    }

    /**
     * @param BumpPeriod $bumpPeriod
     *
     * @return BumpPeriodVote
     */
    public function setBumpPeriod(BumpPeriod $bumpPeriod): BumpPeriodVote
    {
        $this->bumpPeriod = $bumpPeriod;

        return $this;
    }

    /**
     * @return Server
     */
    public function getServer(): Server
    {
        return $this->server;
    }

    /**
     * @param Server $server
     *
     * @return BumpPeriodVote
     */
    public function setServer(Server $server): BumpPeriodVote
    {
        $this->server = $server;

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
     * @return BumpPeriodVote
     */
    public function setDateCreated(DateTime $dateCreated): BumpPeriodVote
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }
}
