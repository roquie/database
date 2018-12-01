<?php declare(strict_types=1);

namespace Roquie\Database\Migrations\Repository;

use InvalidArgumentException;
use PDO;
use Roquie\Database\Migrations\Repository\MigrationRepositoryInterface as MR;

class MigrationRepositoryFactory
{
    /**
     * @param object $database
     * @param string $table
     * @return \Roquie\Database\Migrations\Repository\MigrationRepositoryInterface
     */
    public static function create($database, string $table = MR::DEFAULT_TABLE): MR
    {
        switch (true) {
            case $database instanceof PDO:
                return new SqlMigrationRepository($database, $table);
            default:
                throw new InvalidArgumentException('Database object not supported.');
        }
    }
}
