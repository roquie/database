<?php
/**
 * Created by Roquie.
 * E-mail: roquie0@gmail.com
 * GitHub: Roquie
 * Date: 18/11/2018
 */

namespace Roquie\Database\Migrations;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use Roquie\Database\Migrations\Notify\NotifyFactory;
use Roquie\Database\Migrations\Notify\NotifyInterface;
use Roquie\Database\Migrations\Repository\DatabaseFactory;
use Roquie\Database\Migrations\Repository\MigrationRepositoryFactory;
use Roquie\Database\Migrations\Repository\MigrationRepositoryInterface;

class Migrate
{
    public const DEFAULT_PATH = 'database/migrations';
    public const CHANNEL = 'Rdb';

    /**
     * @var FilesystemInterface
     */
    private $filesystem;

    /**
     * @var \Roquie\Database\Migrations\Repository\MigrationRepositoryInterface
     */
    private $migrationRepository;

    /**
     * @var Notify\NotifyInterface
     */
    private $notify;

    /**
     * Migrate constructor.
     *
     * @param string|object $database
     * @param \League\Flysystem\FilesystemInterface|null $filesystem
     * @param NotifyInterface|string $notify
     */
    public function __construct($database, FilesystemInterface $filesystem, $notify = 'logger')
    {
        $this->migrationRepository = $this->repo($database);
        $this->filesystem = $filesystem;
        $this->notify = NotifyFactory::create($notify);
    }

    /**
     * @param $database
     * @param null|string $source
     * @param NotifyInterface|string|null $notify
     * @return \Roquie\Database\Migrations\Migrate
     */
    public static function new($database, ?string $source = self::DEFAULT_PATH, $notify = 'logger')
    {
        $migrator = new static($database, new Filesystem(new Local($source)), $notify);

        return $migrator;
    }

    /**
     * Returns true if migrations table exists.
     *
     * @return bool
     */
    public function exists(): bool
    {
        return $this->migrationRepository->repositoryExists();
    }

    /**
     * Drop all tables and views in the database.
     *
     * @return \Roquie\Database\Migrations\Migrate
     */
    public function drop(): Migrate
    {
        $this->getMigrator()->drop();

        return $this;
    }

    /**
     * Create the migration repository if table does not exists.
     */
    public function install(): Migrate
    {
        // Create a migration table in the
        // database if it does not exist.
        $this->exists() || $this->migrationRepository->createRepository();

        return $this;
    }

    /**
     * @param array $options
     * @return \Roquie\Database\Migrations\Migrate
     */
    public function run(array $options = []): Migrate
    {
        $this->getMigrator()->migrate($options);

        return $this;
    }

    /**
     * @param array $options
     * @return \Roquie\Database\Migrations\Migrate
     */
    public function rollback(array $options = []): Migrate
    {
        $this->getMigrator()->rollback($options);

        return $this;
    }

    /**
     * @return \Roquie\Database\Migrations\Migrate
     */
    public function reset(): Migrate
    {
        $this->getMigrator()->reset();

        return $this;
    }

    /**
     * @return NotifyInterface
     */
    public function getNotify(): NotifyInterface
    {
        return $this->notify;
    }

    /**
     * @return \Roquie\Database\Migrations\Repository\MigrationRepositoryInterface
     */
    public function getMigrationRepository(): MigrationRepositoryInterface
    {
        return $this->migrationRepository;
    }

    /**
     * @return FilesystemInterface
     */
    public function getFilesystem(): FilesystemInterface
    {
        return $this->filesystem;
    }

    /**
     * @return \Roquie\Database\Migrations\Migrator
     */
    public function getMigrator()
    {
        return new Migrator(
            $this->getMigrationRepository(),
            $this->getFilesystem(),
            $this->getNotify()
        );
    }

    /**
     * @param $database
     * @return \Roquie\Database\Migrations\Repository\MigrationRepositoryInterface
     */
    protected function repo($database): MigrationRepositoryInterface
    {
        if (is_object($database)) {
            return MigrationRepositoryFactory::create($database);
        }

        return MigrationRepositoryFactory::create(
            DatabaseFactory::create($database)
        );
    }
}
