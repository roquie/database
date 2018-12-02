<?php declare(strict_types=1);

namespace Roquie\Database\Console;

use Roquie\Database\Migration\Whois;
use Roquie\Database\Notify\NotifyConsole;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BinMakeCommand extends Command
{

    protected function configure()
    {
        $this
            ->setName('bin:make')
            ->setDescription('Create custom rdb binary file')
            ->setHelp('Example of usage: rdb bin:make');

        $this
            ->addArgument('name', InputArgument::OPTIONAL, 'The name of the binary', 'rdb');
//            ->addOption('path', null, InputOption::VALUE_OPTIONAL, 'The path to the create binary file', './');
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
        Whois::print(new NotifyConsole($output));

        $stub = file_get_contents(__DIR__ . '/stubs/bin.stub');

        if (file_exists($input->getArgument('name'))) {
            $output->writeln('');
            $output->writeln('<info>File exists</info>');
        }

        file_put_contents($input->getArgument('name'), $stub, LOCK_EX);
        chmod($input->getArgument('name'), 0750);

        $output->writeln('');
        $output->writeln('<info>Binary file created</info>');
    }
}
