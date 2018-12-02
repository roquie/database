<?php declare(strict_types=1);

namespace Roquie\Database\Notify;

use Monolog\Handler\ErrorLogHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Roquie\Database\Exception\InvalidArgumentException;
use Symfony\Component\Console\Output\Output;

class NotifyFactory
{
    /**
     * Create a notify object.
     *
     * @param $notify
     * @return NotifyConsole|NotifyLogger|NotifyStdout|NotifyBlackhole
     * @throws \Roquie\Database\Exception\InvalidArgumentException
     */
    public static function create($notify)
    {
        switch (true) {
            case $notify === NotifyInterface::STDOUT:
                return new NotifyStdout();
            case $notify === NotifyInterface::LOGGER:
                return new NotifyLogger(self::defaultLoggerInstance());
            case $notify === NotifyInterface::BLACKHOLE:
                return new NotifyBlackhole();
            case $notify instanceof Output:
                return new NotifyConsole($notify);
            case $notify instanceof LoggerInterface:
                return new NotifyLogger($notify);
            default:
                throw InvalidArgumentException::forNotSupportedNotifier();
        }
    }

    /**
     * Default logger instance.
     *
     * @return \Monolog\Logger
     */
    private static function defaultLoggerInstance()
    {
        $logger = new Logger(NotifyInterface::CHANNEL);
        $logger->pushHandler(new ErrorLogHandler());

        return $logger;
    }
}
