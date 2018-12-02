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

class MigrateDownCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('migrate:down')
            ->setAliases(['migrate:rollback'])
            ->setDescription('Rollback the last database migration')
            ->setHelp('Example of usage: rdb migrate:down --dsn pgsql:dbname=test;host=localhost;user=root');

        $this
            ->addOption('dsn', 'd', InputOption::VALUE_REQUIRED, 'DNS string for connect with database.')
            ->addOption('step', null, InputOption::VALUE_OPTIONAL, 'Force the migrations to be run so they can be rolled back individually')
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
        $options = [
            'step' => $input->getOption('step')
        ];

        $migrate = Migrate::new($input->getOption('dsn'), $input->getOption('path'), $output);
        $migrate->rollback($options);
    }
}
