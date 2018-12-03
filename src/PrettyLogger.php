<?php declare(strict_types=1);

namespace Roquie\Database;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Logger;

class PrettyLogger
{
    public const CHANNEL = 'Rdb';

    /**
     * Create Monolog logger without fucking brackets -> [] []  [] []  [] []  [] []  [] []
     * if context and extra is empty.
     *
     * @param string $channel
     * @return \Monolog\Logger
     */
    public static function create(string $channel = self::CHANNEL): Logger
    {
        $logger = new Logger($channel);
        $handler = new ErrorLogHandler();
        $formatter = new LineFormatter('[%datetime%] %channel%.%level_name%: %message% %context% %extra%');
        $formatter->ignoreEmptyContextAndExtra();

        $handler->setFormatter($formatter);
        $logger->pushHandler($handler);

        return $logger;
    }
}
