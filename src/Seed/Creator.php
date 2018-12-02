<?php declare(strict_types=1);

namespace Roquie\Database\Seed;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use Roquie\Database\Notify\NotifyFactory;
use Roquie\Database\Notify\NotifyInterface;
use Roquie\Database\Migration\Whois;
use Roquie\Database\Seed\Creator\SeedCreator;

final class Creator
{
    /**
     * @var FilesystemInterface
     */
    private $filesystem;

    /**
     * @var NotifyInterface
     */
    private $notify;

    /**
     * @var SeedCreator
     */
    private $creator;

    /**
     * Creator constructor.
     *
     * @param FilesystemInterface|null $filesystem
     * @param NotifyInterface|string $notify
     */
    public function __construct(FilesystemInterface $filesystem, $notify = NotifyInterface::LOGGER)
    {
        $this->filesystem = $filesystem;
        $this->notify = NotifyFactory::create($notify);
        $this->creator = new SeedCreator($this->notify);
    }

    /**
     * @param null|string $source
     * @param \Roquie\Database\Notify\NotifyInterface|string|null $notify
     * @return \Roquie\Database\Seed\Creator
     */
    public static function new(?string $source = Seed::DEFAULT_PATH, $notify = NotifyInterface::LOGGER)
    {
        $seed = new static(new Filesystem(new Local($source)), $notify);

        return $seed;
    }

    /**
     * Create seed class from stub.
     *
     * @param string|null $name
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function create(?string $name = null)
    {
        Whois::print($this->getNotify());

        $this->creator->create($this->filesystem, $name);
    }

    /**
     * @return FilesystemInterface
     */
    public function getFilesystem(): FilesystemInterface
    {
        return $this->filesystem;
    }

    /**
     * @return SeedCreator
     */
    public function getCreator(): SeedCreator
    {
        return $this->creator;
    }

    /**
     * @return NotifyInterface
     */
    public function getNotify(): NotifyInterface
    {
        return $this->notify;
    }
}
