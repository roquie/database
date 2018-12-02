<?php declare(strict_types=1);

namespace Roquie\Database\Migration\Creator;

use League\Flysystem\FilesystemInterface;

interface MigrationCreatorInterface
{
    public const TYPE_UP    = 'up';
    public const TYPE_DOWN  = 'down';
    public const TYPE_BLANK = 'blank';

    /**
     * Create a new migration.
     *
     * @param \League\Flysystem\FilesystemInterface $filesystem
     * @param string $name
     * @param string $table
     * @param bool $create
     * @return void
     */
    public function create(FilesystemInterface $filesystem, string $name, ?string $table = null, bool $create = false): void;
}
