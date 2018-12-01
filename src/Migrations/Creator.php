<?php
/**
 * Created by Roquie.
 * E-mail: roquie0@gmail.com
 * GitHub: Roquie
 * Date: 2018-12-01
 */

namespace Roquie\Database\Migrations;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use Roquie\Database\Migrations\Creator\CreatorFactory;
use Roquie\Database\Migrations\Creator\MigrationCreatorInterface;
use Roquie\Database\Migrations\Notify\NotifyFactory;
use Roquie\Database\Migrations\Notify\NotifyInterface;

class Creator
{
    /**
     * @var \League\Flysystem\FilesystemInterface
     */
    private $filesystem;

    /**
     * @var \Roquie\Database\Migrations\Creator\MigrationCreatorInterface
     */
    private $creator;

    /**
     * @var \Roquie\Database\Migrations\Notify\NotifyInterface|string
     */
    private $notify;

    /**
     * Creator constructor.
     *
     * @param \Roquie\Database\Migrations\Creator\MigrationCreatorInterface $creator
     * @param \League\Flysystem\FilesystemInterface $filesystem
     * @param \Roquie\Database\Migrations\Notify\NotifyInterface|string $notify
     */
    public function __construct(MigrationCreatorInterface $creator, FilesystemInterface $filesystem, $notify = 'logger')
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
     * @param \Roquie\Database\Migrations\Notify\NotifyInterface|string $notify
     * @return \Roquie\Database\Migrations\Creator
     */
    public static function new(string $type = 'default', string $path = Migrate::DEFAULT_PATH, $notify = 'logger'): Creator
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
        $this->creator->create($this->filesystem, ...func_get_args());
    }
}
