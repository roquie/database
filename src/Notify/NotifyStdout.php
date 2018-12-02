<?php declare(strict_types=1);

namespace Roquie\Database\Notify;

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
