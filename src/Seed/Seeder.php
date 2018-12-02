<?php declare(strict_types=1);

namespace Roquie\Database\Seed;

use Invoker\Invoker;
use Invoker\ParameterResolver\Container\ParameterNameContainerResolver;
use Invoker\ParameterResolver\DefaultValueResolver;
use Invoker\ParameterResolver\ResolverChain;
use League\Flysystem\FilesystemInterface;
use Psr\Container\ContainerInterface;
use Roquie\Database\Notify\NotifyInterface;

final class Seeder
{
    /**
     * @var mixed|\PDO
     */
    private $database;

    /**
     * @var \Psr\Container\ContainerInterface
     */
    private $container;

    /**
     * @var \League\Flysystem\FilesystemInterface
     */
    private $filesystem;

    /**
     * @var \Roquie\Database\Notify\NotifyInterface
     */
    private $notify;

    /**
     * @var \Invoker\Invoker
     */
    private $invoker;

    /**
     * Seeder constructor.
     *
     * @param $database
     * @param \Psr\Container\ContainerInterface $container
     * @param \League\Flysystem\FilesystemInterface $filesystem
     * @param \Roquie\Database\Notify\NotifyInterface $notify
     */
    public function __construct(
        $database,
        FilesystemInterface $filesystem,
        NotifyInterface $notify,
        ContainerInterface $container = null
    )
    {
        $invoker = is_null($container)
            ? null
            : $this->invoker($container);

        $this->database = $database;
        $this->filesystem = $filesystem;
        $this->notify = $notify;
        $this->invoker = $invoker;
        $this->container = $container;
    }

    /**
     * Start seeds!
     *
     * @param string|null $name
     * @throws \Invoker\Exception\InvocationException
     * @throws \Invoker\Exception\NotCallableException
     * @throws \Invoker\Exception\NotEnoughParametersException
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function seed(?string $name = null): void
    {
        foreach ($this->all($name) as $file => $content) {
            $this->load($content);
            $this->resolve($file['filename'])->run();

            $this->notify->note("<comment>Seed</comment> {$file['basename']} <comment>executed</comment>");
        }
    }

    /**
     * Resolve loaded Seed class.
     *
     * @param string $class
     * @return \Roquie\Database\Seed\AbstractSeed
     * @throws \Invoker\Exception\InvocationException
     * @throws \Invoker\Exception\NotCallableException
     * @throws \Invoker\Exception\NotEnoughParametersException
     */
    private function resolve(string $class): AbstractSeed
    {
        /** @var $instance \Roquie\Database\Seed\AbstractSeed */
        $instance = $this->autowire($class);
        $instance->setDatabase($this->database);

        if (! is_null($this->container)) {
            $instance->setContainer($this->container);
        }

        return $instance;
    }

    /**
     * Database seed files do not contain user input.
     * So, use eval, in this case, is a normal solution
     * if you want to use Flysystem package.
     *
     * @param string $code
     * @return void
     */
    private function load(string $code): void
    {
        eval($this->removeTags($code));
    }

    /**
     * Remove PHP tags from code for executing in the eval function.
     *
     * @param string $code
     * @return string
     */
    private function removeTags(string $code): string
    {
        return str_replace(['<?php', '?>'], '', $code);
    }

    /**
     * Find all seed's in the default Flysystem path.
     *
     * @param string|null $name
     * @return iterable
     * @throws \League\Flysystem\FileNotFoundException
     */
    private function all(?string $name): iterable
    {
        foreach ($this->filesystem->listContents() as $file) {
            if (is_null($name)) {
                if ($file['filename'] === ($name ?: Seed::DEFAULT_SEED)) {
                    yield $file => $this->filesystem->read($file['path']);
                    break;
                }
            } else {
                yield $file => $this->filesystem->read($file['path']);
            }
        }
    }

    /**
     * Automatically resolve dependencies
     *
     * @param string $class
     * @return mixed
     * @throws \Invoker\Exception\InvocationException
     * @throws \Invoker\Exception\NotCallableException
     * @throws \Invoker\Exception\NotEnoughParametersException
     */
    private function autowire(string $class): AbstractSeed
    {
        if (is_null($this->container)) {
            return new $class();
        }

        return $this->invoker->call($class);
    }

    /**
     * Create Invoker instance for constructor autowiring.
     *
     * @param \Psr\Container\ContainerInterface $container
     * @return \Invoker\Invoker
     */
    private function invoker(ContainerInterface $container)
    {
        $resolvers = new ResolverChain([
            new ParameterNameContainerResolver($container),
            new DefaultValueResolver(),
        ]);

        $invoker = new Invoker($resolvers, $container);

        return $invoker;
    }
}
