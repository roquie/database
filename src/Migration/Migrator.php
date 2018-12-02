<?php

namespace Roquie\Database\Migration;

use League\Flysystem\FilesystemInterface;
use Roquie\Database\Notify\NotifyInterface;
use Roquie\Database\Migration\Creator\MigrationCreatorInterface as M;
use Roquie\Database\Migration\Repository\MigrationRepositoryInterface;
use Roquie\Database\Migration\Repository\SqlMigrationRepository;

class Migrator
{
    /**
     * @var \Roquie\Database\Migration\Repository\MigrationRepositoryInterface
     */
    private $repository;

    /**
     * @var \League\Flysystem\FilesystemInterface
     */
    private $filesystem;

    /**
     * @var \Roquie\Database\Notify\NotifyInterface
     */
    private $notify;

    /**
     * Migrator constructor.
     *
     * @param \Roquie\Database\Migration\Repository\MigrationRepositoryInterface $repository
     * @param \League\Flysystem\FilesystemInterface $filesystem
     * @param \Roquie\Database\Notify\NotifyInterface $notify
     */
    public function __construct(
        MigrationRepositoryInterface $repository,
        FilesystemInterface $filesystem,
        NotifyInterface $notify
    )
    {
        $this->repository = $repository;
        $this->filesystem = $filesystem;
        $this->notify = $notify;
    }

    /**
     * Run the pending migrations.
     *
     * @param  array  $options
     * @return void
     */
    public function migrate(array $options = []): void
    {
        // Once we grab all of the migration files for the path, we will compare them
        // against the migrations that have already been run for this package then
        // run each of the outstanding migrations against a database connection.
        $files = $this->getMigrationFiles(M::TYPE_UP);

        $migrations = $this->pendingMigrations(
            $files, $this->repository->getRan()
        );

        // Once we have all these migrations that are outstanding we are ready to run
        // we will go ahead and run them "up". This will execute each migration as
        // an operation against a database. Then we'll return this list of them.
        $this->runPending($migrations, $options);
    }

    /**
     * Get all of the migration files in a given path.
     *
     * @param string $type
     * @return array
     */
    public function getMigrationFiles(string $type): array
    {
        $array = [];
        foreach ($this->filesystem->listContents() as $file) {
            if ($type === pathinfo($file['filename'], PATHINFO_EXTENSION)) {
                $array[] = $file;
            }
        }

        return $array;
    }

    /**
     * Run an array of migrations.
     *
     * @param  array  $migrations
     * @param  array  $options
     * @return void
     */
    public function runPending(array $migrations, array $options = [])
    {
        // First we will just make sure that there are any migrations to run. If there
        // aren't, we will just make a note of it to the developer so they're aware
        // that all of the migrations have been run against this database system.
        if (count($migrations) === 0) {
            $this->notify->note('<info>Nothing to migrate.</info>');

            return;
        }

        // Next, we will get the next batch number for the migrations so we can insert
        // correct batch number in the database migrations repository when we store
        // each migration's execution. We will also extract a few of the options.
        $batch = $this->repository->getNextBatchNumber();

        $step = $options['step'] ?? false;

        // A blank line before top output.
        $this->notify->note('');

        // Once we have the array of migrations, we will spin through them and run the
        // migrations "up" so the changes are made to the databases. We'll then log
        // that the migration was run so we don't repeat it next time we execute.
        foreach ($migrations as $file) {
            $this->runUp($file, $batch);

            if ($step) {
                $batch++;
            }
        }
    }

    /**
     * Rollback the last migration operation.
     *
     * @param  array $options
     * @return void
     */
    public function rollback(array $options = []): void
    {
        // We want to pull in the last batch of migrations that ran on the previous
        // migration operation. We'll then reverse those migrations and run each
        // of them "down" to reverse the last migration "operation" which ran.
        $migrations = $this->getMigrationsForRollback($options);

        if (count($migrations) === 0) {
            $this->notify->note('<info>Nothing to rollback.</info>');

            return;
        }

        $this->rollbackMigrations($migrations);
    }

    /**
     * Rolls all of the currently applied migrations back.
     *
     * @return void
     */
    public function reset(): void
    {
        // Next, we will reverse the migration list so we can run them back in the
        // correct order for resetting this database. This will allow us to get
        // the database back into its "empty" state ready for the migrations.
        $migrations = array_reverse($this->repository->getRan());

        if (count($migrations) === 0) {
            $this->notify->note('<info>Nothing to rollback.</info>');

            return;
        }

        $this->rollbackMigrations($migrations);
    }

    /**
     * Drops all of tables and views in the database.
     *
     * @return void
     */
    public function drop(): void
    {
        $dropped = $this->repository->drop();

        if (count($dropped) === 0) {
            return;
        }

        $this->notify->note('');

        foreach ($dropped as [$type, $value]) {
            $type = ucfirst($type);
            $this->notify->note("<comment>{$type}</comment> \"{$value}\" <comment>dropped</comment>");
        }
    }

    /**
     * Migration name for database.
     *
     * @param array $file
     * @return string
     */
    public function getMigrationName(array $file): string
    {
        return pathinfo($file['filename'], PATHINFO_FILENAME);
    }

    /**
     * Get the migrations for a rollback operation.
     *
     * @param  array  $options
     * @return array
     */
    protected function getMigrationsForRollback(array $options)
    {
        if (($steps = $options['step'] ?? 0) > 0) {
            return $this->repository->getMigrations($steps);
        }

        return $this->repository->getLast();
    }

    /**
     * Rollback the given migrations.
     *
     * @param  array  $migrations
     * @return void
     */
    protected function rollbackMigrations(array $migrations): void
    {
        // A blank line before top output.
        $this->notify->note('');

        foreach ($this->getMigrationFiles(M::TYPE_DOWN) as $file) {
            if (in_array($name = $this->getMigrationName($file), $migrations, true)) {
                $this->runDown($file);
                continue;
            }

            $this->notify->note("<fg=red>Migrate not found (in database table):</> {$name}");
        }
    }

    /**
     * Run "down" a migration instance.
     *
     * @param  array $file
     * @return void
     */
    protected function runDown(array $file): void
    {
        $this->notify->note("<comment>Rolling back:</comment> {$file['basename']}");

        $this->runMigration($file);

        // Once we have successfully run the migration "down" we will remove it from
        // the migration repository so it will be considered to have not been run
        // by the application then will be able to fire by any later operation.
        $this->repository->delete($this->getMigrationName($file));

        $this->notify->note("<info>Rolled back:</info>  {$file['basename']}");
    }

    /**
     * Run "up" a migration instance.
     *
     * @param array  $file
     * @param int   $batch
     *
     * @return void
     */
    protected function runUp(array $file, int $batch): void
    {
        $this->notify->note("<comment>Migrating:</comment> {$file['basename']}");

        $this->runMigration($file);

        // Once we have run a migrations class, we will log that it was run in this
        // repository so that we don't try to run it next time we do a migration
        // in the application. A migration repository keeps the migrate order.
        $this->repository->log($this->getMigrationName($file), $batch);

        $this->notify->note("<info>Migrated:</info>  {$file['basename']}");
    }

    /**
     * Run a migration inside a transaction if the database supports it.
     *
     * @param array $file
     * @return void
     */
    protected function runMigration(array $file)
    {
        $this->repository->transaction(function (SqlMigrationRepository $repo) use ($file) {
            $contents = (string) $this->filesystem->read($file['path']);
            $repo->execute($contents);
        });
    }

    /**
     * Get the migration files that have not yet run.
     *
     * @param  array  $files
     * @param  array  $ran
     *
     * @return array
     */
    protected function pendingMigrations(array $files, array $ran): array
    {
        $array = [];
        foreach ($files as $file) {
            if (! in_array($this->getMigrationName($file), $ran, true)) {
                $array[] = $file;
            }
        }

        return $array;
    }
}
