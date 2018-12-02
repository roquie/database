<?php declare(strict_types=1);

namespace Roquie\Database\Connection\Exception;

use Roquie\Database\Exception\DatabaseException;

class NotConnectedException extends DatabaseException
{
    public static function forNotConnected(string $dsn)
    {
        return new self(sprintf('Could not connect to database. Credentials [%s].', $dsn));
    }
}
