<?php declare(strict_types=1);

namespace Roquie\Database\Connection\Close;

use PDO;
use Roquie\Database\Exception\InvalidArgumentException;

final class Closer
{
    /**
     * @param $database
     * @return \Roquie\Database\Connection\Close\CloseConnectionInterface
     * @throws \Roquie\Database\Exception\InvalidArgumentException
     */
    public static function database($database): CloseConnectionInterface
    {
        switch ($database) {
            case $database instanceof PDO:
                return new ClosePdoConnection($database);
            default:
                throw InvalidArgumentException::forDatabaseNotSupported();
        }
    }
}
