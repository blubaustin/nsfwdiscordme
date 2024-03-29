<?php
namespace App\Command;

use App\Entity\Server;
use App\Services\DiscordService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Updates the Server::membersOnline value for each server.
 */
class ServerOnlineCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'app:server:online';

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var DiscordService
     */
    protected $discord;

    /**
     * Constructor
     *
     * @param EntityManagerInterface $em
     * @param DiscordService         $discord
     */
    public function __construct(EntityManagerInterface $em, DiscordService $discord)
    {
        parent::__construct();

        $this->em      = $em;
        $this->discord = $discord;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|void|null
     * @throws Exception
     * @throws GuzzleException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $serverRepository = $this->em->getRepository(Server::class);
        foreach ($serverRepository->findAll() as $server) {
            try {
                $online = $this->discord->fetchOnlineCount($server->getDiscordID());
                $server->setMembersOnline($online);
                $output->writeln(sprintf('Updating %s to %d members online.', $server->getDiscordID(), $online));
            } catch (Exception $e) {
                $output->writeln('Error: ' . $e->getMessage());
            }
        }

        $this->em->flush();
        $output->writeln('Done!');
    }
}
