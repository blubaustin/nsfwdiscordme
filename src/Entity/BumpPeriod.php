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
     * @return mixed
     */
    public function getId()
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
