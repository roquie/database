<?php

namespace Roquie\Database\Migration\Repository;

use InvalidArgumentException;
use PDO;

class SqlMigrationRepository implements MigrationRepositoryInterface
{
    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * @var string
     */
    private $table;

    /**
     * SqlMigrationRepository constructor.
     *
     * @param \PDO $pdo
     * @param $table
     */
    public function __construct(PDO $pdo, string $table)
    {
        $this->pdo = $pdo;
        $this->table = $table;
    }

    /**
     * @param string|null $query
     */
    public function execute(?string $query): void
    {
        if (is_null($query)) {
            return;
        }

        $this->pdo->exec($query);
    }

    /**
     * Get the completed migrations.
     *
     * @return array
     */
    public function getRan(): array
    {
        $stmt = $this->pdo->query("select migration from {$this->table} order by batch, migration");
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_COLUMN);

        return $results;
    }

    /**
     * Get list of migrations.
     *
     * @param  int $steps
     * @return array
     */
    public function getMigrations(int $steps): array
    {
        $sql = "select migration from {$this->table} 
                where batch >= 1 
                order by batch, migration desc
                limit ?";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(1, $steps, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Get the last migration batch.
     *
     * @return array
     */
    public function getLast(): array
    {
        $sql = "select migration from {$this->table} as b
                where exists (select max(batch) from {$this->table} as a where b.batch = a.batch) 
                order by migration desc";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Get the completed migrations with their batch numbers.
     *
     * @return array
     */
    public function getMigrationBatches(): array
    {
        $stmt = $this->pdo->prepare("select * from {$this->table} order by batch, migration");
        $stmt->execute();

        $array = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $item) {
            $array[$item['migration']] = $item['batch'];
        }

        return $array;
    }

    /**
     * Log that a migration was run.
     *
     * @param  string $file
     * @param  int $batch
     * @return void
     */
    public function log(string $file, int $batch): void
    {
        $stmt = $this->pdo->prepare("insert into {$this->table} (migration, batch) values (?, ?)");
        $stmt->bindParam(1, $file);
        $stmt->bindParam(2, $batch, PDO::PARAM_INT);
        $stmt->execute();
    }

    /**
     * Remove a migration from the log.
     *
     * @param  string $migration
     * @return void
     */
    public function delete(string $migration): void
    {
        $stmt = $this->pdo->prepare("delete from {$this->table} where migration = ?");
        $stmt->bindParam(1, $migration);
        $stmt->execute();
    }

    /**
     * Get the next migration batch number.
     *
     * @return int
     */
    public function getNextBatchNumber(): int
    {
        return $this->getLastBatchNumber() + 1;
    }

    /**
     * Get the last migration batch number.
     *
     * @return int
     */
    public function getLastBatchNumber(): int
    {
        $stmt = $this->pdo->query("select max(batch) from {$this->table}");
        $stmt->execute();

        return (int) $stmt->fetch(PDO::FETCH_ASSOC)['max'];
    }

    /**
     * Create the migration repository data store.
     *
     * @return void
     */
    public function createRepository(): void
    {
        $sql = "CREATE TABLE {$this->table} ( 
                    migration Character Varying (255) NOT NULL,
                    batch Integer NOT NULL
                );";

        $this->pdo->exec($sql);
    }

    /**
     * Determine if the migration repository exists.
     *
     * @return bool
     */
    public function repositoryExists(): bool
    {
        switch ($this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME)) {
            case 'pgsql':
                $sql = 'select count(*) from information_schema.tables 
                        where table_schema = current_schema() 
                        and table_name = ?';
                break;
            case 'mysql':
                $sql = 'select count(*) from information_schema.tables 
                        where table_schema = database() 
                        and table_name = ?';
                break;
            case 'sqlsrv':
                $sql = "select count(*) from sysobjects where type = 'U' and name = ?";
                break;
            case 'sqlite':
                $sql = "select count(*) from sqlite_master where type = 'table' and name = ?";
                break;
            default:
                throw new InvalidArgumentException('Database not supported.');
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(1, $this->table);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_COLUMN) > 0;
    }

    /**
     * Wraps the queries inside callback into transaction.
     *
     * @param callable $callback
     * @return void
     */
    public function transaction(callable $callback): void
    {
        $this->pdo->beginTransaction();
        $callback($this);
        $this->pdo->commit();
    }

    /**
     * Drop all views and tables.
     */
    public function drop(): array
    {
        $touched = [];
        $this->pdo->beginTransaction();

        foreach ($this->getViews() as $view) {
            $this->pdo->exec("drop view if exists {$view} cascade");
            $touched[] = ['view', $view];
        }

        foreach ($this->getTables() as $table) {
            $this->pdo->exec("drop table if exists {$table} cascade");
            $touched[] = ['table', $table];
        }

        $this->pdo->commit();

        return $touched;
    }

    /**
     * @return array
     */
    private function getTables(): array
    {
        switch ($this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME)) {
            case 'pgsql':
                $sql = 'select table_name from information_schema.tables where table_schema = current_schema()';
                break;
            case 'mysql':
                $sql = 'select table_name from information_schema.tables where table_schema = database()';
                break;
            case 'sqlsrv':
                $sql = "select name from sysobjects where type = 'U'";
                break;
            case 'sqlite':
                $sql = "select name from sqlite_master where type = 'table'";
                break;
            default:
                throw new InvalidArgumentException('Database not supported.');
        }

        return $this->pdo
            ->query($sql)
            ->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * @return array
     */
    private function getViews(): array
    {
        switch ($this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME)) {
            case 'pgsql':
                $sql = 'select table_name from information_schema.views where table_schema = current_schema()';
                break;
            case 'mysql':
                // TODO mysql not tested
                $sql = 'select table_name from information_schema.views where table_schema = database()';
                break;
            case 'sqlsrv':
                // TODO sqlsrv not tested
                $sql = "select name from sysobjects where type = 'v'";
                break;
            case 'sqlite':
                // TODO sqlite not tested
                $sql = "select name from sqlite_master where type = 'view'";
                break;
            default:
                throw new InvalidArgumentException('Database not supported.');
        }

        return $this->pdo
            ->query($sql)
            ->fetchAll(PDO::FETCH_COLUMN);
    }
}
