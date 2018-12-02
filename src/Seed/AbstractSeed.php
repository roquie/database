<?php declare(strict_types=1);

namespace Roquie\Database\Seed;

use InvalidArgumentException;
use Psr\Container\ContainerInterface;

abstract class AbstractSeed
{
    /**
     * PSR-compatible DI container for autowiring constructor dependencies.
     *
     * @var \Psr\Container\ContainerInterface
     */
    protected $container;

    /**
     * Any database driver.
     * If u use relational databases this is PDO instance.
     *
     * @var mixed|\PDO
     */
    protected $database;

    /**
     * Run database seeds here.
     */
    abstract public function run(): void;

    /**
     * PSR-compatible DI container for autowiring constructor dependencies.
     *
     * @param \Psr\Container\ContainerInterface $container
     * @return AbstractSeed
     */
    public function setContainer(ContainerInterface $container): AbstractSeed
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Any database driver.
     * If u use relational databases this is PDO instance.
     *
     * @param mixed|\PDO $database
     * @return AbstractSeed
     */
    public function setDatabase($database)
    {
        $this->database = $database;

        return $this;
    }

    /**
     * PSR-compatible DI container for autowiring constructor dependencies.
     *
     * @return \Psr\Container\ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * Any database driver.
     * If u use relational databases this is PDO instance.
     *
     * @return mixed|\PDO
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * Return Seed instance for fill
     * database and container objects.
     *
     * @return $this
     */
    public function __invoke()
    {
        return $this;
    }

    /**
     * Call seeder.
     *
     * @param $class
     * @return void
     */
    protected function call(string $class): void
    {
        if (! $this->getContainer()->has($class)) {
            throw new InvalidArgumentException('Seeder not registered in the container.');
        }

        $instance = $this->getContainer()->get($class);

        if (! $instance instanceof AbstractSeed) {
            throw new InvalidArgumentException('Seeder class must be extended from AbstractSeed.');
        }

        $instance->run();
    }
}
