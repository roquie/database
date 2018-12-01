<?php
/**
 * Created by Roquie.
 * E-mail: roquie0@gmail.com
 * GitHub: Roquie
 * Date: 23/11/2018
 */

namespace Roquie\Database\Console;

use Roquie\Database\Migrations\Migrate;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateRefreshCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('migrate:refresh')
            ->setDescription('Reset and re-run all migrations')
            ->setHelp('Example of usage: rdb migrate:refresh --dns pgsql:dbname=test;host=localhost;user=root');

        $this
            ->addOption('dsn', 'd', InputOption::VALUE_REQUIRED, 'DNS string for connect with database.')
            ->addOption('step', null, InputOption::VALUE_OPTIONAL, 'Force the migrations to be run so they can be rolled back individually', 0)
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

        if (! $migrate->exists()) {
            $output->writeln('<comment>Migration table not found.</comment>');
            return;
        }

        $step = $input->getOption('step');

        $step > 0 ? $migrate->rollback(compact('step')) : $migrate->reset();

        $migrate->run();

        $output->writeln('');
        $output->writeln('<info>Done</info>');
    }
}