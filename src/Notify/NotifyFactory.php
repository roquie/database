<?php declare(strict_types=1);

namespace Roquie\Database\Notify;

use Psr\Log\LoggerInterface;
use Roquie\Database\Exception\InvalidArgumentException;
use Roquie\Database\PrettyLogger;
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
                return new NotifyLogger(PrettyLogger::create());
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
}
