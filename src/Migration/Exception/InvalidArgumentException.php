<?php declare(strict_types=1);

namespace Roquie\Database\Migration\Exception;

use Roquie\Database\Exception\MigrateException;

class InvalidArgumentException extends MigrateException
{
    /**
     * @param string $type
     * @return \Roquie\Database\Migration\Exception\InvalidArgumentException
     */
    public static function forDatabaseTypeNotSupported(string $type)
    {
        return new self(sprintf('Database type [%s] not supported.', $type));
    }

    public static function forDatabaseObjectNotSupported()
    {
        return new self('Database object not supported.');
    }

    public static function forDatabaseNotSupported()
    {
        return new self('Database not supported.');
    }
}
