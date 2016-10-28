<?php


namespace Anime\Command;


use Sequence\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class DatabaseBackupCommand extends ContainerAwareCommand
{
    /** @var  string */
    private $backupPath;

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        if(!$this->getContainer()->has('database'))
        {
            return false;
        }

        return parent::isEnabled();
    }

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this
            ->setName('database:backup')
            ->setDescription('Makes default database backup.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $database = $this->getDatabaseSettings();

        if('mysql' !== $database['type'])
        {
            return 47;
        }

        $this->backupPath = $this->getContainer()->get('config')->framework->get('root_dir').'/backups';

        system('mysqldump -h '.$database['host'].' -u '.$database['username'].' -p'.$database['password'].' '.$database['dbname'].' | gzip > '.$this->backupPath.'/'.$database['dbname'].'.'.date('Ymd').'.sql.gz');

        /** @var \Sequence\Mail\Mailer $mail */
        $mail = $this->getContainer()->get('mailer');
        $mail->addAddress('lexarks@gmail.com');
        $mail->Subject = 'Daily backup at AnimeZone.pl';
        $mail->Body = $this->getContainer()->get('templating')->render('Mail/backup', array(
            'title' => 'Your daily database backup was created!',
        ));
        $mail->send();

        $this->clearOldBackups();

        $output->writeln('<info>Backup file created!</info>');

        return 0;
    }

    /**
     * @return array
     */
    private function getDatabaseSettings()
    {
        $settings = $this->getContainer()->get('database_manager')->getConnectionSettings('animezone_md');

        if(empty($settings))
        {
            throw new \InvalidArgumentException('Default database settings are empty.');
        }

        preg_match_all('/(?P<type>\w+):host=(?P<host>[^;]+);?|dbname=(?P<dbname>[^;]+);?/', $settings['dsn'], $matches);

        return array(
            'username' => $settings['username'],
            'password' => $settings['password'],
            'type' => $matches['type'][0] ?: $matches['type'][1],
            'host' => $matches['host'][0] ?: $matches['host'][1],
            'dbname' => $matches['dbname'][0] ?: $matches['dbname'][1],
        );
    }

    /**
     * @param int $days
     */
    private function clearOldBackups($days = 9)
    {
        $finder = new Finder();
        $iterator = $finder
            ->files()
            ->name('*.gz')
            ->date('after '.$days.' day')
            ->depth(0)
            ->in($this->backupPath);

        $filesystem = new Filesystem();

        foreach($iterator as $file)
        {
            $filesystem->remove($file->getRealPath());
        }
    }
} 