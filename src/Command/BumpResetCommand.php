<?php
namespace App\Command;

use App\Entity\Server;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class BumpResetCommand
 */
class BumpResetCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'app:bumps:reset';

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
        $day = date('d');
        if ($day !== '01' && $day !== '15') {
            $output->writeln('Nothing to do!');
            return;
        }

        foreach($this->em->getRepository(Server::class)->findAll() as $server) {
            $server->setBumpPoints(0);
            $output->writeln('Resetting ' . $server->getDiscordID());
        }

        $this->em->flush();
        $output->writeln('Done!');
    }
}
