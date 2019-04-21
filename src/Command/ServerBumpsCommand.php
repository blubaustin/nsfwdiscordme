<?php
namespace App\Command;

use App\Entity\Server;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ServerBumpsCommand
 */
class ServerBumpsCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'app:server:bumps';

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
/*        $now = new DateTime();
        $serverRepository = $this->em->getRepository(Server::class);
        foreach($serverRepository->findAll() as $server) {
            if ($server->getDateNextBump() < $now) {
                $server->setDateNextBump(new DateTime('24 hours'));
            }
        }

        $this->em->flush();*/

        $output->writeln('Done!');
    }
}
