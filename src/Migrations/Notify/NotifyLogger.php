<?php

namespace Roquie\Database\Migrations\Notify;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class NotifyLogger implements NotifyInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Logger level.
     *
     * @var string
     */
    private $level;

    /**
     * NotifyLogger constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param string $level
     */
    public function __construct(LoggerInterface $logger, string $level = LogLevel::INFO)
    {
        $this->logger = $logger;
        $this->level = $level;
    }

    /**
     * Notify user about actions.
     *
     * @param string $message
     * @return void
     */
    public function note(string $message): void
    {
        $this->logger->log($this->level, strip_tags($message));
    }
}
