<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use DateTime;
use Exception;

/**
 * @ORM\Table(name="server_view_event")
 * @ORM\Entity(repositoryClass="App\Repository\ServerViewEventRepository")
 */
class ServerViewEvent
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
     * @return ServerViewEvent
     */
    public function setServer(Server $server): ServerViewEvent
    {
        $this->server = $server;

        return $this;
    }

    /**
     * @return resource|string
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
     * @param resource|string $ip
     *
     * @return ServerViewEvent
     */
    public function setIp($ip): ServerViewEvent
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * @param string $ipString
     *
     * @return $this
     */
    public function setIpString($ipString): ServerViewEvent
    {
        $this->ip = inet_pton($ipString);

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
     * @return ServerViewEvent
     */
    public function setDateCreated(DateTime $dateCreated): ServerViewEvent
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }
}
