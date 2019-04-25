<?php
namespace App\Command;

use App\Discord\Discord;
use App\Entity\Server;
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
     * @var Discord
     */
    protected $discord;

    /**
     * Constructor
     *
     * @param EntityManagerInterface $em
     * @param Discord                $discord
     */
    public function __construct(EntityManagerInterface $em, Discord $discord)
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
