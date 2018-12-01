<?php

namespace Roquie\Database\Migration\Repository;

use InvalidArgumentException;
use PDO;

class DatabaseFactory
{
    /**
     * @param string $dns
     * @return \PDO|mixed
     */
    public static function create(string $dns)
    {
        switch (parse_url($dns, PHP_URL_SCHEME)) {
            case 'pgsql':
            case 'mysql':
            case 'sqlsrv':
            case 'sqlite':
                return self::defaultPdoInstance($dns);
            default:
                throw new InvalidArgumentException('Database dsn not supported.');
        }
    }

    /**
     * @param string $dsn
     * @return \PDO
     */
    private static function defaultPdoInstance(string $dsn): PDO
    {
        $pdo = new PDO($dsn);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        return $pdo;
    }
}
