<?php
/**
 * Created by Roquie.
 * E-mail: roquie0@gmail.com
 * GitHub: Roquie
 * Date: 23/11/2018
 */

namespace Roquie\Database\Console;

use Roquie\Database\Migration\Migrate;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Roquie\Database\Migration\Creator\MigrationCreatorInterface as M;

class MigrateFreshCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('migrate:fresh')
            ->setDescription('Drop all tables and re-run all migrations')
            ->setHelp('Example of usage: rdb migrate:fresh --dns pgsql:dbname=test;host=localhost;user=root');

        $this
            ->addOption('dsn', 'd', InputOption::VALUE_REQUIRED, 'DNS string for connect with database.')
            ->addOption('path', null, InputOption::VALUE_OPTIONAL, 'The path to the migrations files to use', Migrate::DEFAULT_PATH);
    }

    /**
     * Execute command, captain.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $migrate = Migrate::new($input->getOption('dsn'), $input->getOption('path'), $output);

        $files = $migrate->getMigrator()->getMigrationFiles(M::TYPE_UP);
        $migrate->drop();

        if (count($files) < 1) {
            $output->writeln('');
            $output->writeln('<comment>Migration files not found</comment>');
            return;
        }

        $migrate
            ->install()
            ->run();

        $output->writeln('');
        $output->writeln('<info>Database is fresh</info>');
    }
}
