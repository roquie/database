<?php
/**
 * Created by Roquie.
 * E-mail: roquie0@gmail.com
 * GitHub: Roquie
 * Date: 23/11/2018
 */

namespace Roquie\Database\Console;

use Roquie\Database\Migrations\Creator;
use Roquie\Database\Migrations\Migrate;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateMakeCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('make:migration')
            ->setDescription('Create a new migration files')
            ->setHelp('Example of usage: rdb make:migration create_users_table --create users');

        $this
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the migration')
            ->addOption('type', null, InputOption::VALUE_OPTIONAL, 'Database type.', 'default')
            ->addOption('create', 'c', InputOption::VALUE_OPTIONAL, 'The table to be created', false)
            ->addOption('table', 't', InputOption::VALUE_OPTIONAL, 'The table to migrate')
            ->addOption('path', 'p', InputOption::VALUE_OPTIONAL, 'The location where the migration file should be created', Migrate::DEFAULT_PATH);
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
        $name = trim($input->getArgument('name'));
        $table = $input->getOption('table');
        $create = $input->getOption('create') ?: false;
        $path = $input->getOption('path');
        $type = $input->getOption('type');

        // If no table was given as an option but a create option is given then we
        // will use the "create" option as the table name. This allows the devs
        // to pass a table name into this option as a short-cut for creating.
        if (! $table && is_string($create)) {
            $table = $create;

            $create = true;
        }

        $creator = Creator::new($type, $path, $output);
        $creator->create($name, $table, $create);
    }
}
