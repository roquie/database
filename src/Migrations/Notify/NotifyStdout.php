<?php

namespace Roquie\Database\Migrations\Notify;

class NotifyStdout implements NotifyInterface
{
    /**
     * Notify user about actions.
     *
     * @param string $message
     * @return void
     */
    public function note(string $message): void
    {
        fwrite(STDOUT, strip_tags($message) . PHP_EOL);
    }
}
