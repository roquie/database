<?php declare(strict_types=1);

namespace Roquie\Database\Console;

use Psr\Container\ContainerInterface;
use Roquie\Database\Migration\Migrate;
use Roquie\Database\Seed\Seed;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateUpCommand extends Command
{
    /**
     * @var \Psr\Container\ContainerInterface
     */
    private $container;

    /**
     * MigrateUpCommand constructor.
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
            ->setName('migrate:up')
            ->setDescription('Run the database migrations')
            ->setHelp('Example of usage: rdb migrate:up --dsn pgsql:dbname=test;host=localhost;user=root');

        $this
            ->addOption('dsn', 'd', InputOption::VALUE_REQUIRED, 'DNS string for connect with database.')
            ->addOption('drop', null, InputOption::VALUE_NONE, 'Drop database before run the database migrations.')
            ->addOption('step', null, InputOption::VALUE_OPTIONAL, 'Force the migrations to be run so they can be rolled back individually.')
            ->addOption('seed', null, InputOption::VALUE_NONE, 'Indicates if the seed task should be re-run.')
            ->addOption('path', null, InputOption::VALUE_OPTIONAL, 'The path to the migrations files to use.', Migrate::DEFAULT_PATH);
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
        $options = [
            'step' => $input->getOption('step')
        ];

        $migrate = Migrate::new($input->getOption('dsn'), $input->getOption('path'), $output);

        if ($input->getOption('drop')) {
            $migrate->drop();
        }

        $migrate
            ->install()
            ->run($options)
            ->close();

        if ($input->getOption('seed')) {
            $this->getSeed($input, $output)
                 ->run()
                 ->close();
        }
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return \Roquie\Database\Seed\Seed
     */
    private function getSeed(InputInterface $input, OutputInterface $output): Seed
    {
        $seed = Seed::new($input->getOption('dsn'), Seed::DEFAULT_PATH, $output);
        if ($this->container instanceof ContainerInterface) {
            $seed->setContainer($this->container);
        }

        return $seed;
    }
}
