<?php declare(strict_types=1);

namespace Roquie\Database\Console;

use Psr\Container\ContainerInterface;
use Roquie\Database\Migration\Migrate;
use Roquie\Database\Seed\Seed;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Roquie\Database\Migration\Creator\MigrationCreatorInterface as M;

class MigrateFreshCommand extends Command
{
    /**
     * @var \Psr\Container\ContainerInterface
     */
    private $container;

    /**
     * MigrateFreshCommand constructor.
     *
     * @param \Psr\Container\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container = null)
    {
        $this->container = $container;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('migrate:fresh')
            ->setDescription('Drop all tables and re-run all migrations')
            ->setHelp('Example of usage: rdb migrate:fresh --dsn pgsql:dbname=test;host=localhost;user=root');

        $this
            ->addOption('dsn', 'd', InputOption::VALUE_REQUIRED, 'DNS string for connect with database.')
            ->addOption('seed', null, InputOption::VALUE_OPTIONAL, 'Indicates if the seed task should be re-run.')
            ->addOption('path', null, InputOption::VALUE_OPTIONAL, 'The path to the migrations files to use', Migrate::DEFAULT_PATH);
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
            ->run()
            ->close();

        if ($input->getOption('seed')) {
            $this->getSeed($input, $output)
                 ->run($input->getOption('seed'))
                 ->close();
        }

        $output->writeln('');
        $output->writeln('<comment>Completed.</comment>');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return \Roquie\Database\Seed\Seed
     */
    private function getSeed(InputInterface $input, OutputInterface $output)
    {
        $seed = Seed::new($input->getOption('dsn'), Seed::DEFAULT_PATH, $output);
        if ($this->container instanceof ContainerInterface) {
            $seed->setContainer($this->container);
        }

        return $seed;
    }
}
