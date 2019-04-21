<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use DateTime;
use Exception;

/**
 * @ORM\Table(name="bump_server_event")
 * @ORM\Entity()
 */
class BumpServerEvent
{
    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", options={"unsigned"=true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var Server
     * @ORM\ManyToOne(targetEntity="Server")
     * @ORM\JoinColumn(name="server_id", onDelete="CASCADE", referencedColumnName="id")
     */
    protected $server;

    /**
     * @var string|resource
     * @ORM\Column(type="binary", length=16)
     */
    protected $ip;

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
     * @return Server
     */
    public function getServer(): Server
    {
        return $this->server;
    }

    /**
     * @param Server $server
     *
     * @return BumpServerEvent
     */
    public function setServer(Server $server): BumpServerEvent
    {
        $this->server = $server;

        return $this;
    }

    /**
     * @return resource
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @return string
     */
    public function getIpString(): string
    {
        rewind($this->ip);
        return stream_get_contents($this->ip);
    }

    /**
     * @param string $ip
     *
     * @return BumpServerEvent
     */
    public function setIp(string $ip): BumpServerEvent
    {
        $this->ip = $ip;

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
     * @return BumpServerEvent
     */
    public function setDateCreated(DateTime $dateCreated): BumpServerEvent
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }
}
