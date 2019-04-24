<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use DateTime;
use Exception;

/**
 * @ORM\Table(name="server_bump_event")
 * @ORM\Entity(repositoryClass="App\Repository\ServerBumpEventRepository")
 */
class ServerBumpEvent
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
     * @return ServerBumpEvent
     */
    public function setServer(Server $server): ServerBumpEvent
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
     * @return ServerBumpEvent
     */
    public function setIp(string $ip): ServerBumpEvent
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * @param string $ipString
     *
     * @return $this
     */
    public function setIpString($ipString): ServerBumpEvent
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
     * @return ServerBumpEvent
     */
    public function setDateCreated(DateTime $dateCreated): ServerBumpEvent
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }
}
