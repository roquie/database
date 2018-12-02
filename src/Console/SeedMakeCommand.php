<?php declare(strict_types=1);

namespace Roquie\Database\Console;

use Roquie\Database\Seed\Creator;
use Roquie\Database\Seed\Seed;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SeedMakeCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('seed:make')
            ->setDescription('Create a new seeder class')
            ->setHelp('Example of usage: rdb seed:make UserSeeder');

        $this
            ->addArgument('name', InputArgument::OPTIONAL, 'The class name of the seeder', Seed::DEFAULT_SEED)
            ->addOption('path', null, InputOption::VALUE_OPTIONAL, 'The path to the seeds files to use', Seed::DEFAULT_PATH);
    }

    /**
     * Execute command, captain.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|null|void
     * @throws \League\Flysystem\FileNotFoundException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $creator = Creator::new($input->getOption('path'), $output);
        $creator->create($input->getArgument('name'));

        exec('composer dump-autoload');

        $output->writeln('');
        $output->writeln('<comment>Ok</comment>');
    }
}
