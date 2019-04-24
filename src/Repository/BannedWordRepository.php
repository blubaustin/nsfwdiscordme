<?php
namespace App\Repository;

use App\Entity\BannedWord;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class BannedWordRepository
 */
class BannedWordRepository extends ServiceEntityRepository
{
    /**
     * Constructor
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, BannedWord::class);
    }

    /**
     * @param int $id
     *
     * @return object|BannedWord
     */
    public function findByID($id)
    {
        return $this->findOneBy(['id' => $id]);
    }

    /**
     * @param string $text
     *
     * @return bool
     */
    public function containsBannedWords($text)
    {
        /** @var BannedWord[] $banned */
        $banned = $this->findAll();
        foreach($banned as $ban) {
            $word = preg_quote($ban->getWord(), '/');
            if (preg_match("/\b(${word})\b/i", $text)) {
                return true;
            }
        }

        return false;
    }
}
