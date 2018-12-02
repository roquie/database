<?php declare(strict_types=1);

namespace Roquie\Database\Seed\Creator;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use Roquie\Database\Notify\NotifyInterface;
use Roquie\Database\Seed\Seed;

final class SeedCreator
{
    /**
     * @var \League\Flysystem\Filesystem
     */
    private $stubs;

    /**
     * @var \Roquie\Database\Notify\NotifyInterface
     */
    private $notify;

    /**
     * SeedCreator constructor.
     *
     * @param \Roquie\Database\Notify\NotifyInterface $notify
     */
    public function __construct(NotifyInterface $notify)
    {
        $this->stubs = new Filesystem(new Local($this->stubPath()));
        $this->notify = $notify;
    }

    /**
     * Create a new migration.
     *
     * @param \League\Flysystem\FilesystemInterface $filesystem
     * @param string|null $name
     * @return void
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function create(FilesystemInterface $filesystem, ?string $name = null): void
    {
        $stub = $this->stubs->read('new.php.stub');
        $filename = $this->getName($name).'.php';

        if ($filesystem->has($filename)) {
            $this->notify->note('');
            $this->notify->note("<info>Seed with name</info> {$filename} <info>already exists.</info>");
            return;
        }

        $filesystem->put($filename, $this->populateStub($stub, $name));

        $this->notify->note('');
        $this->notify->note("<comment>Seed</comment> {$filename} <comment>created</comment>");
    }

    /**
     * Populate the place-holders in the seed stub.
     *
     * @param  string $stub
     * @param string|null $class
     * @return string
     */
    protected function populateStub(string $stub, ?string $class = null): string
    {
        return str_replace('{class}', $this->getName($class), $stub);
    }

    /**
     * @param string|null $name
     * @return string
     */
    protected function getName(?string $name = null)
    {
        return $this->camelize($this->camelize($name), '-') ?: Seed::DEFAULT_SEED;
    }

    /**
     * @param string $input
     * @param string $separator
     * @return string
     */
    protected function camelize(string $input, $separator = '_'): string
    {
        return str_replace($separator, '', ucwords($input, $separator));
    }

    /**
     * Get the path to the stubs.
     *
     * @return string
     */
    protected function stubPath(): string
    {
        return __DIR__ . '/stubs';
    }
}
