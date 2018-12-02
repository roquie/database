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
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Roquie\Database\Migration\Creator\MigrationCreatorInterface as M;

class MigrateStatusCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('migrate:status')
            ->setDescription('Show the status of each migration')
            ->setHelp('Example of usage: rdb migrate:status --dsn pgsql:dbname=test;host=localhost;user=root');

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

        if (! $migrate->exists()) {
            $output->writeln('<error>Migration table not found.</error>');
            return;
        }

        $ran = $migrate->getMigrationRepository()->getRan();
        $batches = $migrate->getMigrationRepository()->getMigrationBatches();
        $files = $migrate->getMigrator()->getMigrationFiles(M::TYPE_UP);

        if ($this->hasMigrations($files, $ran)) {
            $output->writeln('<error>No migrations found.</error>');
            return;
        }

        $table = new Table($output);
        $table->setHeaders(['Ran?', 'Migration', 'Batch']);
        $table->setRows($this->table($migrate, $files, $ran, $batches));

        $table->render();
    }

    /**
     * Check if migrations files or database migration rows exist.
     *
     * @param array $files
     * @param array $ran
     *
     * @return bool
     */
    private function hasMigrations(array $files, array $ran)
    {
        return count($files) === 0 || count($ran) === 0;
    }

    /**
     * @param \Roquie\Database\Migration\Migrate $migrate
     * @param array $files
     * @param array $ran
     * @param array $batches
     * @return array
     */
    private function table(Migrate $migrate, array $files, array $ran, array $batches): array
    {
        $content = [];
        foreach ($files as $migration) {
            $name = $migrate
                ->getMigrator()
                ->getMigrationName($migration);

            $content[] = in_array($name, $ran)
                ? ['<info>Yes</info>', $name, $batches[$name]]
                : ['<fg=red>No</fg=red>', $name];
        }

        return $content;
    }
}
