<?php

namespace Roquie\Database\Migrations\Notify;

use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\Output;

class NotifyConsole implements NotifyInterface
{
    /**
     * @var \Symfony\Component\Console\Output\Output
     */
    private $output;

    /**
     * NotifyConsole constructor.
     *
     * @param \Symfony\Component\Console\Output\Output $output
     */
    public function __construct(Output $output)
    {
        $this->output = $output;
    }

    /**
     * Notify user about actions.
     *
     * @param string $message
     * @return void
     */
    public function note(string $message): void
    {
        $this->output->writeln($message);
    }
}
