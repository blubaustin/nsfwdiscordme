<?php
namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use PHPGangsta_GoogleAuthenticator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Class UserPromoteCommand
 */
class UserRoleAddCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'app:user:role-add';

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
     *
     */
    protected function configure()
    {
        $this->setDescription('Adds a role to a user.');
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
        $repo   = $this->em->getRepository(User::class);
        $helper = $this->getHelper('question');

        $question = new Question('Discord email or ID: ', false);
        if (!($email = $helper->ask($input, $output, $question))) {
            return;
        }

        if (is_numeric($email)) {
            $user = $repo->findByDiscordID($email);
        } else {
            $user = $repo->findByDiscordEmail($email);
        }
        if (!$user) {
            $output->writeln('User not found.');
        }

        $question = new Question('Role to add, i.e. ROLE_ADMIN: ', false);
        if (!($role = $helper->ask($input, $output, $question))) {
            return;
        }

        $user->addRole($role);
        $this->em->flush();
        $output->writeln('Role added. The user should log out and log back in now.');

        if (strtoupper($role) === User::ROLE_ADMIN) {
            $googleAuthenticator = new PHPGangsta_GoogleAuthenticator();
            $secret = $googleAuthenticator->createSecret();
            $user->setGoogleAuthenticatorSecret($secret);
            $this->em->flush();

            $qr = $googleAuthenticator->getQRCodeGoogleUrl('nsfwdiscord.me', $secret);
            $output->writeln('Google Authenticator secret: ' . $qr);
        }
    }
}
