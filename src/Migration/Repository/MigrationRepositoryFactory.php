<?php declare(strict_types=1);

namespace Roquie\Database\Migration\Repository;

use PDO;
use Roquie\Database\Migration\Exception\InvalidArgumentException;
use Roquie\Database\Migration\Repository\MigrationRepositoryInterface as MR;

class MigrationRepositoryFactory
{
    /**
     * @param object $database
     * @param string $table
     * @return \Roquie\Database\Migration\Repository\MigrationRepositoryInterface
     * @throws \Roquie\Database\Migration\Exception\InvalidArgumentException
     */
    public static function create($database, string $table = MR::DEFAULT_TABLE): MR
    {
        switch (true) {
            case $database instanceof PDO:
                return new SqlMigrationRepository($database, $table);
            default:
                throw InvalidArgumentException::forDatabaseObjectNotSupported();
        }
    }
}
