<?php declare(strict_types=1);

namespace Roquie\Database\Migration;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use Roquie\Database\Migration\Creator\CreatorFactory;
use Roquie\Database\Migration\Creator\MigrationCreatorInterface;
use Roquie\Database\Notify\NotifyFactory;
use Roquie\Database\Notify\NotifyInterface;

class Creator
{
    /**
     * @var FilesystemInterface
     */
    private $filesystem;

    /**
     * @var MigrationCreatorInterface
     */
    private $creator;

    /**
     * @var \Roquie\Database\Notify\NotifyInterface|string
     */
    private $notify;

    /**
     * Creator constructor.
     *
     * @param MigrationCreatorInterface $creator
     * @param FilesystemInterface $filesystem
     * @param \Roquie\Database\Notify\NotifyInterface|string $notify
     */
    public function __construct(
        MigrationCreatorInterface $creator,
        FilesystemInterface $filesystem,
        $notify = NotifyInterface::LOGGER
    )
    {
        $note = $notify instanceof NotifyInterface
            ? $notify
            : NotifyFactory::create($notify);

        $this->creator = $creator;
        $this->filesystem = $filesystem;
        $this->notify = $note;
    }

    /**
     * Create a Creator object with default values.
     *
     * @param string $type
     * @param string $path
     * @param \Roquie\Database\Notify\NotifyInterface|string $notify
     * @return \Roquie\Database\Migration\Creator
     */
    public static function new(
        string $type = 'default',
        string $path = Migrate::DEFAULT_PATH,
        $notify = NotifyInterface::LOGGER
    ): Creator
    {
        $fs = new Filesystem(new Local($path));
        $note = NotifyFactory::create($notify);

        return new static(CreatorFactory::create($type, $note), $fs, $note);
    }

    /**
     * Create a new migration.
     *
     * @param string $name
     * @param null|string $table
     * @param bool $create
     * @return void
     */
    public function create(string $name, ?string $table = null, bool $create = false): void
    {
        Whois::print($this->getNotify());

        $this->creator->create($this->filesystem, ...func_get_args());
    }

    /**
     * @return \Roquie\Database\Notify\NotifyInterface|string
     */
    public function getNotify()
    {
        return $this->notify;
    }

    /**
     * @return MigrationCreatorInterface
     */
    public function getCreator(): MigrationCreatorInterface
    {
        return $this->creator;
    }

    /**
     * @return FilesystemInterface
     */
    public function getFilesystem(): FilesystemInterface
    {
        return $this->filesystem;
    }
}
