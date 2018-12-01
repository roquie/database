<?php

namespace Roquie\Database\Migration\Creator;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use Roquie\Database\Migration\Creator\MigrationCreatorInterface as M;
use Roquie\Database\Migration\Notify\NotifyInterface;

class MigrationCreator implements MigrationCreatorInterface
{
    /**
     * @var \League\Flysystem\FilesystemInterface
     */
    private $stubs;

    /**
     * @var \Roquie\Database\Migration\Notify\NotifyInterface
     */
    private $notify;

    /**
     * MigrationCreator constructor.
     *
     * @param \Roquie\Database\Migration\Notify\NotifyInterface $notify
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
     * @param string $name
     * @param string $table
     * @param bool $create
     * @return void
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function create(FilesystemInterface $filesystem, string $name, ?string $table = null, bool $create = false): void
    {
        foreach ($this->getStubs($table, $create) as $type => $stub) {
            $filesystem->put(
                $filename = $this->getFilename($name, $type),
                $this->populateStub($filename, $stub, $table)
            );

            $this->notify->note("<comment>Stub</comment> {$filename} <comment>created</comment>");
        }
    }

    /**
     * Get the migration stub file.
     *
     * @param  string $table
     * @param  bool $create
     * @return iterable
     * @throws \League\Flysystem\FileNotFoundException
     */
    protected function getStubs(?string $table = null, bool $create = false): iterable
    {
        if (is_null($table)) {
            yield M::TYPE_UP => $this->stubs->read('blank.sql.stub');
            yield M::TYPE_DOWN => $this->stubs->read('blank.sql.stub');
            return;
        }

        $first = [M::TYPE_UP => 'create.sql.stub', M::TYPE_DOWN => 'down.sql.stub'];
        $second = [M::TYPE_UP => 'update.sql.stub', M::TYPE_DOWN => 'update.sql.stub'];

        $stubs = $create ? $first : $second;

        foreach ($stubs as $type => $stub) {
            yield $type => $this->stubs->read($stub);
        }
    }

    /**
     * Populate the place-holders in the migration stub.
     *
     * @param  string  $filename
     * @param  string  $stub
     * @param  string  $table
     * @return string
     */
    protected function populateStub(string $filename, string $stub, ?string $table = null): string
    {
        $search = ['{name}', '{table}'];
        $replace = [$filename, $table ?: 'dummy_table'];

        return str_replace($search, $replace, $stub);
    }

    /**
     * Get the migrations file names.
     *
     * @param  string $name
     * @param string $type
     * @return string
     */
    protected function getFilename(string $name, string $type): string
    {
        return $this->getDatePrefix() . "_{$name}.{$type}.sql";
    }

    /**
     * Get the date prefix for the migration.
     *
     * @return string
     */
    protected function getDatePrefix(): string
    {
        return date('Y_m_d_His');
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
