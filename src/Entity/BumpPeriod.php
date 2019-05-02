<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use DateTime;
use Exception;

/**
 * @ORM\Table(name="bump_period")
 * @ORM\Entity(repositoryClass="App\Repository\BumpPeriodRepository")
 */
class BumpPeriod
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="bigint", options={"unsigned"=true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var DateTime
     * @ORM\Column(type="datetime", unique=true)
     */
    protected $date;

    /**
     * Constructor
     *
     * @throws Exception
     */
    public function __construct()
    {
        $this->date = new DateTime();
    }

    /**
     * @return string
     */
    public function getFormattedDate()
    {
        return $this->getDate()->format('Y-m-d H:i:s');
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return DateTime
     */
    public function getDate(): DateTime
    {
        return $this->date;
    }

    /**
     * @param DateTime $date
     *
     * @return BumpPeriod
     */
    public function setDate(DateTime $date): BumpPeriod
    {
        $this->date = $date;

        return $this;
    }
}
