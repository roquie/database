<?php declare(strict_types=1);

namespace Roquie\Database\Console;

use Psr\Container\ContainerInterface;
use Roquie\Database\Migration\Whois;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\ConsoleOutput;

final class Console
{
    /**
     * Run console application.
     *
     * @param $argv
     * @param \Psr\Container\ContainerInterface|null $container
     * @throws \Exception
     */
    public static function main($argv, ContainerInterface $container = null)
    {
        $output = self::cyanLine($argv);

        $app = new Application('');
        $app->add(new MigrateMakeCommand());
        $app->add(new MigrateUpCommand($container));
        $app->add(new MigrateDownCommand());
        $app->add(new MigrateResetCommand());
        $app->add(new MigrateStatusCommand());
        $app->add(new MigrateRefreshCommand());
        $app->add(new MigrateFreshCommand($container));
        $app->add(new MigrateDropCommand());
        $app->add(new SeedRunCommand($container));
        $app->add(new SeedMakeCommand());
        $app->add(new BinMakeCommand());

        $app->run(null, $output);
    }

    /**
     * @param $argv
     * @return \Symfony\Component\Console\Output\ConsoleOutput
     */
    private static function cyanLine($argv): ConsoleOutput
    {
        $output = new ConsoleOutput();
        $output->getFormatter()->setStyle('cyan', new OutputFormatterStyle('cyan'));

        if (self::isPrint($argv)) {
            $output->writeln(Whois::WHOIS);
            $output->writeln('');
        }

        return $output;
    }

    /**
     * @param $argv
     * @return bool
     */
    private static function isPrint($argv): bool
    {
        return (isset($argv[1]) && in_array($argv[1], ['list', 'help'], true))
            || empty($argv[1]);
    }
}
