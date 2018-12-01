<?php
/**
 * Created by Roquie.
 * E-mail: roquie0@gmail.com
 * GitHub: Roquie
 * Date: 2018-12-01
 */

namespace Roquie\Database\Migrations\Notify;

use InvalidArgumentException;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\Output;

class NotifyFactory
{
    /**
     * Create a notify object.
     *
     * @param $notify
     * @return NotifyConsole|NotifyLogger|NotifyStdout|NotifyBlackhole
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
                throw new InvalidArgumentException('Notifier not supported.');
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
