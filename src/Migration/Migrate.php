<?php declare(strict_types=1);

namespace Roquie\Database\Migration;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use Roquie\Database\Notify\NotifyFactory;
use Roquie\Database\Notify\NotifyInterface;
use Roquie\Database\DatabaseFactory;
use Roquie\Database\Migration\Repository\MigrationRepositoryFactory;
use Roquie\Database\Migration\Repository\MigrationRepositoryInterface;

class Migrate
{
    public const DEFAULT_PATH = 'database/migrations';
    public const CHANNEL = 'Rdb';

    /**
     * @var FilesystemInterface
     */
    private $filesystem;

    /**
     * @var \Roquie\Database\Migration\Repository\MigrationRepositoryInterface
     */
    private $migrationRepository;

    /**
     * @var \Roquie\Database\Notify\NotifyInterface
     */
    private $notify;

    /**
     * Migrate constructor.
     *
     * @param string|object $database
     * @param \League\Flysystem\FilesystemInterface|null $filesystem
     * @param \Roquie\Database\Notify\NotifyInterface|string $notify
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
     * @return \Roquie\Database\Migration\Migrate
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
        Whois::print($this->getNotify());

        return $this->migrationRepository->repositoryExists();
    }

    /**
     * Drop all tables and views in the database.
     *
     * @return \Roquie\Database\Migration\Migrate
     */
    public function drop(): Migrate
    {
        Whois::print($this->getNotify());

        $this->getMigrator()->drop();

        return $this;
    }

    /**
     * Create the migration repository if table does not exists.
     */
    public function install(): Migrate
    {
        Whois::print($this->getNotify());

        // Create a migration table in the
        // database if it does not exist.
        $this->exists() || $this->migrationRepository->createRepository();

        return $this;
    }

    /**
     * @param array $options
     * @return \Roquie\Database\Migration\Migrate
     */
    public function run(array $options = []): Migrate
    {
        Whois::print($this->getNotify());

        $this->getMigrator()->migrate($options);

        return $this;
    }

    /**
     * @param array $options
     * @return \Roquie\Database\Migration\Migrate
     */
    public function rollback(array $options = []): Migrate
    {
        Whois::print($this->getNotify());

        $this->getMigrator()->rollback($options);

        return $this;
    }

    /**
     * @return \Roquie\Database\Migration\Migrate
     */
    public function reset(): Migrate
    {
        Whois::print($this->getNotify());

        $this->getMigrator()->reset();

        return $this;
    }

    /**
     * Close database connection after all operations.
     */
    public function close(): void
    {
        $this->getMigrationRepository()->close();
    }

    /**
     * @return \Roquie\Database\Notify\NotifyInterface
     */
    public function getNotify(): NotifyInterface
    {
        return $this->notify;
    }

    /**
     * @return \Roquie\Database\Migration\Repository\MigrationRepositoryInterface
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
     * @return \Roquie\Database\Migration\Migrator
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
     * @return \Roquie\Database\Migration\Repository\MigrationRepositoryInterface
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
