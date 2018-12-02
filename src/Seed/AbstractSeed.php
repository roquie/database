<?php declare(strict_types=1);

namespace Roquie\Database\Seed;

use Psr\Container\ContainerInterface;

abstract class AbstractSeed
{
    /**
     * PSR-compatible DI container for autowiring constructor dependencies.
     *
     * @var \Psr\Container\ContainerInterface|null
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
     * @var Seeder
     */
    protected $seeder;

    /**
     * Run database seeds here.
     */
    abstract public function run(): void;

    /**
     * PSR-compatible DI container for autowiring constructor dependencies.
     *
     * @param \Psr\Container\ContainerInterface $container
     * @return void
     */
    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    /**
     * Any database driver.
     * If u use relational databases this is PDO instance.
     *
     * @param mixed|\PDO $database
     * @return void
     */
    public function setDatabase($database): void
    {
        $this->database = $database;
    }

    /**
     * @param Seeder $seeder
     * @return void
     */
    public function setSeeder(Seeder $seeder): void
    {
        $this->seeder = $seeder;
    }

    /**
     * PSR-compatible DI container for autowiring constructor dependencies.
     *
     * @return \Psr\Container\ContainerInterface|null
     */
    public function getContainer(): ?ContainerInterface
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
     * @param string $class
     * @return void
     * @throws \Invoker\Exception\InvocationException
     * @throws \Invoker\Exception\NotCallableException
     * @throws \Invoker\Exception\NotEnoughParametersException
     * @throws \League\Flysystem\FileNotFoundException
     * @throws \Roquie\Database\Seed\Exception\InvalidArgumentException
     */
    protected function call(string $class): void
    {
        $this->seeder->call($class);
    }
}
