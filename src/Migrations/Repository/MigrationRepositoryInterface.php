<?php

namespace Roquie\Database\Migrations\Repository;

interface MigrationRepositoryInterface
{
    public const DEFAULT_TABLE = 'migrations';

    /**
     * @param string|null $query
     */
    public function execute(?string $query): void;

    /**
     * Get the completed migrations.
     *
     * @return array
     */
    public function getRan(): array;

    /**
     * Get list of migrations.
     *
     * @param  int  $steps
     * @return array
     */
    public function getMigrations(int $steps);

    /**
     * Get the last migration batch.
     *
     * @return array
     */
    public function getLast(): array;

    /**
     * Get the completed migrations with their batch numbers.
     *
     * @return array
     */
    public function getMigrationBatches(): array ;

    /**
     * Log that a migration was run.
     *
     * @param  string  $file
     * @param  int  $batch
     * @return void
     */
    public function log(string $file, int $batch): void;

    /**
     * Remove a migration from the log.
     *
     * @param  string  $migration
     * @return void
     */
    public function delete(string $migration): void;

    /**
     * Get the next migration batch number.
     *
     * @return int
     */
    public function getNextBatchNumber(): int;

    /**
     * Get the last migration batch number.
     *
     * @return int
     */
    public function getLastBatchNumber(): int;

    /**
     * Create the migration repository data store.
     *
     * @return void
     */
    public function createRepository(): void;

    /**
     * Determine if the migration repository exists.
     *
     * @return bool
     */
    public function repositoryExists(): bool;

    /**
     * Wraps the queries inside callback into transaction.
     *
     * @param callable $callback
     *
     * @return void
     */
    public function transaction(callable $callback): void;

    /**
     * Drop all views and tables.
     *
     * @return array
     */
    public function drop(): array;
}
