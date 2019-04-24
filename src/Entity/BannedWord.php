<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use DateTime;
use Exception;

/**
 * @ORM\Table(name="banned_word",
 *     uniqueConstraints={
 *          @ORM\UniqueConstraint(columns={"word"})
 *     }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\BannedWordRepository")
 */
class BannedWord
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned"=true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=60, nullable=true)
     */
    protected $word;

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
     * @return string
     */
    public function getWord(): string
    {
        return $this->word;
    }

    /**
     * @param string $word
     *
     * @return BannedWord
     */
    public function setWord(string $word): BannedWord
    {
        $this->word = $word;

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
     * @return BannedWord
     */
    public function setDateCreated(DateTime $dateCreated): BannedWord
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }
}
