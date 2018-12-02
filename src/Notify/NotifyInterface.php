<?php declare(strict_types=1);

namespace Roquie\Database\Notify;

interface NotifyInterface
{
    public const CHANNEL = 'Rdb';

    public const STDOUT = 'stdout';
    public const LOGGER = 'logger';
    public const BLACKHOLE = 'backhole';

    /**
     * Notify user about actions.
     *
     * @param string $message
     * @return void
     */
    public function note(string $message): void;
}
