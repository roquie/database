<?php declare(strict_types=1);

namespace Roquie\Database\Seed;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use Psr\Container\ContainerInterface;
use Roquie\Database\Notify\NotifyFactory;
use Roquie\Database\Notify\NotifyInterface;
use Roquie\Database\Migration\Repository\DatabaseFactory;
use Roquie\Database\Migration\Whois;

class Seed
{
    public const DEFAULT_PATH = 'database/seeds';
    public const DEFAULT_SEED = 'DatabaseSeeder';

    /**
     * @var mixed|\PDO
     */
    private $database;

    /**
     * @var ContainerInterface|null
     */
    private $container;

    /**
     * @var FilesystemInterface
     */
    private $filesystem;

    /**
     * @var NotifyInterface
     */
    private $notify;

    /**
     * Migrate constructor.
     *
     * @param string|object $database
     * @param ContainerInterface $container
     * @param FilesystemInterface|null $filesystem
     * @param NotifyInterface|string $notify
     */
    public function __construct(
        $database,
        FilesystemInterface $filesystem,
        ContainerInterface $container = null,
        $notify = NotifyInterface::LOGGER
    )
    {
        $this->database = DatabaseFactory::create($database);
        $this->container = $container;
        $this->filesystem = $filesystem;
        $this->notify = NotifyFactory::create($notify);
    }

    /**
     * @param $database
     * @param null|string $source
     * @param NotifyInterface|string|null $notify
     * @return \Roquie\Database\Seed\Seed
     */
    public static function new($database, ?string $source = self::DEFAULT_PATH, $notify = NotifyInterface::LOGGER): Seed
    {
        $seed = new static($database, new Filesystem(new Local($source)), null, $notify);

        return $seed;
    }

    /**
     * @param string|null $name
     * @return void
     * @throws \Invoker\Exception\InvocationException
     * @throws \Invoker\Exception\NotCallableException
     * @throws \Invoker\Exception\NotEnoughParametersException
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function run(?string $name = null): void
    {
        Whois::print($this->getNotify());

        $this->getSeeder()->seed($name);
    }

    /**
     * @param ContainerInterface $container
     * @return Seed
     */
    public function setContainer(ContainerInterface $container): Seed
    {
        $this->container = $container;

        return $this;
    }

    /**
     * @return \Roquie\Database\Seed\Seeder
     */
    public function getSeeder(): Seeder
    {
        return new Seeder(
            $this->getDatabase(),
            $this->getFilesystem(),
            $this->getNotify(),
            $this->getContainer()
        );
    }

    /**
     * @return NotifyInterface
     */
    public function getNotify(): NotifyInterface
    {
        return $this->notify;
    }

    /**
     * @return FilesystemInterface
     */
    public function getFilesystem(): FilesystemInterface
    {
        return $this->filesystem;
    }

    /**
     * @return mixed|\PDO
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * @return ContainerInterface|null
     */
    public function getContainer(): ?ContainerInterface
    {
        return $this->container;
    }
}
