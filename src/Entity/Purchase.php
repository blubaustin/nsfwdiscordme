<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use DateTime;
use Exception;

/**
 * @ORM\Table(name="purchase", indexes={@ORM\Index(columns={"purchase_token"})})
 * @ORM\Entity(repositoryClass="App\Repository\PurchaseRepository")
 */
class Purchase
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="bigint", options={"unsigned"=true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var Server
     * @ORM\ManyToOne(targetEntity="Server")
     * @ORM\JoinColumn(name="server_id", onDelete="CASCADE", referencedColumnName="id", nullable=false)
     */
    protected $server;

    /**
     * @var string
     * @ORM\Column(type="string", length=32)
     */
    protected $purchaseToken;

    /**
     * @var int
     * @ORM\Column(type="smallint")
     */
    protected $status;

    /**
     * @var int
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
    protected $period;

    /**
     * @var int
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
    protected $price;

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
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
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
     * @return Purchase
     */
    public function setServer(Server $server): Purchase
    {
        $this->server = $server;

        return $this;
    }

    /**
     * @return string
     */
    public function getPurchaseToken(): string
    {
        return $this->purchaseToken;
    }

    /**
     * @param string $purchaseToken
     *
     * @return Purchase
     */
    public function setPurchaseToken(string $purchaseToken): Purchase
    {
        $this->purchaseToken = $purchaseToken;

        return $this;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param int $status
     *
     * @return Purchase
     */
    public function setStatus(int $status): Purchase
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return int
     */
    public function getPeriod(): int
    {
        return $this->period;
    }

    /**
     * @param int $period
     *
     * @return Purchase
     */
    public function setPeriod(int $period): Purchase
    {
        $this->period = $period;

        return $this;
    }

    /**
     * @return int
     */
    public function getPrice(): int
    {
        return $this->price;
    }

    /**
     * @param int $price
     *
     * @return Purchase
     */
    public function setPrice(int $price): Purchase
    {
        $this->price = $price;

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
     * @return Purchase
     */
    public function setDateCreated(DateTime $dateCreated): Purchase
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }
}
