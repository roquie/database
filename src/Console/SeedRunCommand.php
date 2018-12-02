<?php declare(strict_types=1);

namespace Roquie\Database\Console;

use Psr\Container\ContainerInterface;
use Roquie\Database\Migration\Migrate;
use Roquie\Database\Seed\Seed;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SeedRunCommand extends Command
{
    /**
     * Application container if needed.
     *
     * @var \Psr\Container\ContainerInterface
     */
    private $container;

    /**
     * SeedRunCommand constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container = null)
    {
        $this->container = $container;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('seed:run')
            ->setDescription('Seed the database with records')
            ->setHelp('Example of usage: rdb seed:run --dsn pgsql:dbname=test;host=localhost;user=root');

        $this
            ->addArgument('name', InputArgument::OPTIONAL, 'The class name of the root seeder')
            ->addOption('dsn', 'd', InputOption::VALUE_REQUIRED, 'DNS string for connect with database.')
            ->addOption('path', null, InputOption::VALUE_OPTIONAL, 'The path to the seeds files to use', Seed::DEFAULT_PATH);
    }

    /**
     * Execute command, captain.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|null|void
     * @throws \Invoker\Exception\InvocationException
     * @throws \Invoker\Exception\NotCallableException
     * @throws \Invoker\Exception\NotEnoughParametersException
     * @throws \League\Flysystem\FileNotFoundException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $seed = Seed::new($input->getOption('dsn'), $input->getOption('path'), $output);

        if ($this->container instanceof ContainerInterface) {
            $seed->setContainer($this->container);
        }

        $seed->run($input->getArgument('name'));

        $output->writeln('');
        $output->writeln('<info>Seed completed</info>');
    }
}
