<?php declare(strict_types=1);

namespace Roquie\Database\Exception;

class InvalidArgumentException extends DatabaseException
{
    /**
     * @param string $dsn
     * @return \Roquie\Database\Exception\InvalidArgumentException
     */
    public static function forDnsNotSupported(string $dsn)
    {
        return new self(sprintf('Database dsn [%s] not supported.', $dsn));
    }

    public static function forDatabaseNotSupported()
    {
        return new self(sprintf('Database not supported.'));
    }

    public static function forNotSupportedNotifier()
    {
        return new self('Notifier not supported.');
    }
}
