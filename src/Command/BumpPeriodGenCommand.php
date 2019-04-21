<?php
namespace App\Command;

use App\Entity\BumpPeriod;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use DateInterval;
use DateTime;
use Exception;

/**
 * Populates the bump_period table.
 */
class BumpPeriodGenCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'app:bumps:gen-periods';

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * Constructor
     *
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|void|null
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $date = new DateTime('2019-04-19 00:00:00');
        $day  = new DateInterval('P1D');

        // Generate bump periods for 5,000 days.
        for ($i = 0; $i < 5000; $i++) {
            $date       = $date->add($day)->setTime(0, 0, 0);
            $bumpPeriod = (new BumpPeriod())->setDate($date);
            $this->em->persist($bumpPeriod);
            $this->em->flush();
            $output->writeln($date->format('Y-m-d H:i:s'));

            $date       = $date->setTime(6, 0, 0);
            $bumpPeriod = (new BumpPeriod())->setDate($date);
            $this->em->persist($bumpPeriod);
            $this->em->flush();
            $output->writeln($date->format('Y-m-d H:i:s'));

            $date       = $date->setTime(12, 0, 0);
            $bumpPeriod = (new BumpPeriod())->setDate($date);
            $this->em->persist($bumpPeriod);
            $this->em->flush();
            $output->writeln($date->format('Y-m-d H:i:s'));

            $date       = $date->setTime(18, 0, 0);
            $bumpPeriod = (new BumpPeriod())->setDate($date);
            $this->em->persist($bumpPeriod);
            $this->em->flush();
            $output->writeln($date->format('Y-m-d H:i:s'));
        }

        $output->writeln('Done!');
    }
}
