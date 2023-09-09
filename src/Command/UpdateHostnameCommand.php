<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;

use App\Entity\Settings;
use App\Enumerations\RoleEnumeration;
use App\Exceptions\RoleNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Stopwatch\Stopwatch;
use function Symfony\Component\String\u;

#[AsCommand(
    name: 'app:set-hostname',
    description: 'Updates the hostname setting in the database'
)]
class UpdateHostnameCommand extends Command
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            // commands can optionally define arguments and/or options (mandatory and optional)
            // see https://symfony.com/doc/current/components/console/console_arguments.html
            ->addArgument('hostname', InputArgument::OPTIONAL, 'The hostname of the server')
        ;
    }

    /**
     * This optional method is the first one executed for a command after configure()
     * and is useful to initialize properties based on the input arguments and options.
     */
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        // SymfonyStyle is an optional feature that Symfony provides so you can
        // apply a consistent look to the commands of your application.
        // See https://symfony.com/doc/current/console/style.html
        $this->io = new SymfonyStyle($input, $output);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        if (null !== $input->getArgument('hostname')) {
            return;
        }

        $this->io->title('Set Hostname');

        // Ask for the username if it's not defined
        $hostname = $input->getArgument('hostname');
        if (null !== $hostname) {
            $this->io->text(' > <info>Hostname</info>: ' . $hostname);
        } else {
            $hostname = $this->io->ask('Hostname', null);
            $input->setArgument('hostname', $hostname);
        }
    }


    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws RoleNotFoundException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $stopwatch = new Stopwatch();
        $stopwatch->start('set-hostname-command');

        /** @var string $hostname */
        $hostname = $input->getArgument('hostname');

        /**
         * @var Settings $hostsetting
         */
        $hostsetting = $this->entityManager->getRepository(Settings::class)->findOneBy(['setting' => 'server_url']);
        $hostsetting->setValue($hostname);

        $this->entityManager->persist($hostsetting);
        $this->entityManager->flush();

        $this->io->success(sprintf('Hostname has been updated to: %s', $hostname));

        $event = $stopwatch->stop('set-hostname-command');
        if ($output->isVerbose()) {
            $this->io->comment(sprintf('Elapsed time: %.2f ms / Consumed memory: %.2f MB', $event->getDuration(), $event->getMemory() / (1024 ** 2)));
        }

        return Command::SUCCESS;
    }

}