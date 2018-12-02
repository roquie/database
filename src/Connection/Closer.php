<?php declare(strict_types=1);

namespace Roquie\Database\Connection;

use PDO;

final class Closer
{
    /**
     * @param $database
     * @return \Roquie\Database\Connection\CloseConnectionInterface
     */
    public static function database($database): CloseConnectionInterface
    {
        switch ($database) {
            case $database instanceof PDO:
                return new ClosePdoConnection($database);
            default:
                throw new \InvalidArgumentException('Database not supported');
        }
    }
}
