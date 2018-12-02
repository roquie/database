<?php declare(strict_types=1);

namespace Roquie\Database\Connection\Wait;

use Monolog\Handler\ErrorLogHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Roquie\Database\Connection\Exception\NotConnectedException;

final class Wait
{
    private const CHANNEL = 'WaitDB';
    protected const DEFAULT_ATTEMPTS = 10;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Roquie\Database\Connection\Wait\WaitInterface
     */
    private $connection;

    /**
     * @var integer
     */
    private $attempt = self::DEFAULT_ATTEMPTS;

    /**
     * Wait constructor.
     *
     * @param \Psr\Log\LoggerInterface|null $logger
     */
    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger ?: $this->logger();
        $this->logger->info('Wait connection to database...');
    }

    /**
     * @param \Roquie\Database\Connection\Wait\WaitInterface $connection
     * @return \Roquie\Database\Connection\Wait\Wait
     */
    public function with(WaitInterface $connection): Wait
    {
        $this->connection = $connection;

        return $this;
    }

    /**
     * @param int $count
     * @return \Roquie\Database\Connection\Wait\Wait
     */
    public function attempts(int $count)
    {
        $this->attempt = $count;

        return $this;
    }

    /**
     * @return \Roquie\Database\Connection\Wait\Wait
     */
    public static function new()
    {
        return new static();
    }

    /**
     * @param string $dsn
     * @param array $params
     */
    public static function connection(string $dsn, ...$params)
    {
        if (count($params) === 1) {
            $callback = $params[0];
        } else {
            [$attempts, $callback] = $params;
        }

        $wait = new static();
        $wait->with(new PdoWait());
        $wait->attempts($attempts ?? self::DEFAULT_ATTEMPTS);
        $wait->start($dsn, $callback);

        return;
    }

    /**
     * @param string $dsn
     * @param callable $callback
     */
    public function start(string $dsn, callable $callback)
    {
        $completed = 0;
        while ($completed <= $this->attempt) {
            try {
                $database = $this->connection->alive($dsn);
            } catch (NotConnectedException $e) {
                $this->logger->warn(sprintf('%s, attempt no. %d', $e->getMessage(), $completed + 1));
                continue;
            }

            if (is_object($database)) {
                $this->logger->info('Connected successfully.');
                $callback($database);
                break;
            }

            $completed++;
        }
    }

    /**
     * @return \Monolog\Logger
     */
    private function logger()
    {
        $logger = new Logger(self::CHANNEL);
        $logger->pushHandler(new ErrorLogHandler());

        return $logger;
    }
}
