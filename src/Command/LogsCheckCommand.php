<?php
namespace App\Command;

use Exception;
use Redis;
use SplFileObject;
use Swift_Mailer;
use Swift_Message;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Scans the log files and sends an email to the sysadmin when errors are found.
 */
class LogsCheckCommand extends Command
{
    const EMAIL_FROM = 'no-reply@headzoo.io';
    const EMAIL_TO   = 'sean@headzoo.io';
    const SUBJECT    = '[nsfwdiscordme log check]';

    /**
     * @var string
     */
    protected static $defaultName = 'app:logs:check';

    /**
     * @var string
     */
    protected $logFile;

    /**
     * @var Redis
     */
    protected $redis;

    /**
     * @var Swift_Mailer
     */
    protected $mailer;

    /**
     * Constructor
     *
     * @param string       $logFile
     * @param Redis        $redis
     * @param Swift_Mailer $mailer
     */
    public function __construct($logFile, Redis $redis, Swift_Mailer $mailer)
    {
        parent::__construct();
        $this->logFile = $logFile;
        $this->redis   = $redis;
        $this->mailer  = $mailer;
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
        $this->redis->select(0);
        $lastLine = (int)$this->redis->get('app:logs:check:lastLine');
        $output->writeln(sprintf('Starting with line %d.', $lastLine));

        $file = new SplFileObject($this->logFile);
        $file->seek($lastLine);

        $reports     = [];
        $currentLine = 0;
        while (!$file->eof()) {
            $currentLine++;
            if (preg_match('/^\[(.*?)\] ([\w]+)\.(CRITICAL|ERROR|WARNING): (.*)$/', $file->current(), $matches)) {
                $reports[] = $matches;
            }
            $file->next();
        }

        $countReports = count($reports);
        if ($countReports) {
            $this->sendReports($reports, $output);
        }

        $this->redis->set('app:logs:check:lastLine', $currentLine);
        $output->writeln(sprintf('Done! %d reports found. Last line = %d.', $countReports, $currentLine));
    }

    /**
     * @param array           $reports
     * @param OutputInterface $output
     */
    protected function sendReports(array $reports, OutputInterface $output)
    {
        $found = [];
        $counts = [
            'CRITICAL' => 0,
            'ERROR'    => 0,
            'WARNING'  => 0
        ];
        foreach($reports as $report) {
            $counts[$report[3]]++;
            if (!$this->containsReport($found, $report)) {
                $found[] = $report;
            }
        }

        $message = '';
        foreach($found as $value) {
            $message .= $value[0] . "\n";
        }

        if ($message) {
            $message = sprintf(
                "Found %d CRITICAL, %d ERROR, %d WARNING\n\n%s",
                $counts['CRITICAL'],
                $counts['ERROR'],
                $counts['WARNING'],
                $message
            );

            $output->writeln('Sending report.');
            $swiftMessage = (new Swift_Message())
                ->setFrom(self::EMAIL_FROM)
                ->setTo(self::EMAIL_TO)
                ->setSubject(self::SUBJECT)
                ->setBody($message);
            $this->mailer->send($swiftMessage);
        }
    }

    /**
     * @param array $found
     * @param array $report
     *
     * @return bool
     */
    protected function containsReport(array $found, array $report)
    {
        $needle = substr($report[4], 0, 50);
        foreach($found as $p) {
            if (substr($p[4], 0, 50) === $needle) {
                return true;
            }
        }

        return false;
    }
}
