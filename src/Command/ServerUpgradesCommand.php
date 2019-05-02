<?php
namespace App\Command;

use App\Entity\PurchasePeriod;
use App\Entity\Server;
use App\Repository\PurchasePeriodRepository;
use App\Repository\ServerRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ServerUpgradesCommand
 */
class ServerUpgradesCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'app:server:upgrades';

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var PurchasePeriodRepository
     */
    protected $periodRepo;

    /**
     * @var ServerRepository
     */
    protected $serverRepo;

    /**
     * Constructor
     *
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();

        $this->em         = $em;
        $this->periodRepo = $em->getRepository(PurchasePeriod::class);
        $this->serverRepo = $em->getRepository(Server::class);
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
        foreach($this->periodRepo->findExpired() as $purchasePeriod) {
            $server = $purchasePeriod->getPurchase()->getServer();
            if ($server->getPremiumStatus() !== Server::STATUS_STANDARD) {
                $output->writeln(
                    sprintf('Expiring %s.', $server->getDiscordID())
                );
                $this->expire($purchasePeriod);
            }
        }

        $this->em->flush();

        foreach($this->periodRepo->findReady() as $purchasePeriod) {
            $server = $purchasePeriod->getPurchase()->getServer();
            if ($server->getPremiumStatus() === Server::STATUS_STANDARD) {
                $output->writeln(
                    sprintf('Upgrading %s.', $server->getDiscordID())
                );
                $this->upgrade($purchasePeriod);
            }
        }

        $this->em->flush();

        $output->writeln('Done!');
    }

    /**
     * @param PurchasePeriod $purchasePeriod
     */
    private function expire(PurchasePeriod $purchasePeriod)
    {
        $server = $purchasePeriod->getPurchase()->getServer();
        $server->setPremiumStatus(Server::STATUS_STANDARD);
        $purchasePeriod->setIsComplete(true);
    }

    /**
     * @param PurchasePeriod $purchasePeriod
     *
     * @throws Exception
     */
    private function upgrade(PurchasePeriod $purchasePeriod)
    {
        $purchase = $purchasePeriod->getPurchase();
        $server   = $purchasePeriod->getPurchase()->getServer();
        $server->setPremiumStatus($purchase->getPremiumStatus());

        $period = $purchase->getPeriod();
        $purchasePeriod
            ->setDateBegins(new DateTime())
            ->setDateExpires(new DateTime("${period} days"));
    }
}
