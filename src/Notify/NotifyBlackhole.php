<?php declare(strict_types=1);

namespace Roquie\Database\Notify;

class NotifyBlackhole implements NotifyInterface
{
    /**
     * Notify user about actions.
     *
     * @param string $message
     * @return void
     */
    public function note(string $message): void
    {
        // to black hole...
    }
}
