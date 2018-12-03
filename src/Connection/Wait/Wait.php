<?php declare(strict_types=1);

namespace Roquie\Database\Connection\Wait;

use Psr\Log\LoggerInterface;
use Roquie\Database\Connection\Exception\NotConnectedException;
use Roquie\Database\PrettyLogger;

final class Wait
{
    private const CHANNEL = 'WaitDB';
    private const DEFAULT_ATTEMPTS = 10;
    private const DEFAULT_EVERY_SECOND = 3;

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
     * @var integer
     */
    private $every = self::DEFAULT_EVERY_SECOND;

    /**
     * @var bool
     */
    private static $disableExit = false;

    /**
     * Wait constructor.
     *
     * @param \Psr\Log\LoggerInterface|null $logger
     */
    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger ?: PrettyLogger::create(self::CHANNEL);
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
     * @param int $second
     * @return \Roquie\Database\Connection\Wait\Wait
     */
    public function every(int $second)
    {
        $this->every = $second;

        return $this;
    }

    /**
     * @return \Roquie\Database\Connection\Wait\Wait
     */
    public static function new()
    {
        return new static();
    }

    public static function disableExitOnFail()
    {
        static::$disableExit = true;
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
        while ($completed < $this->attempt) {
            try {
                $database = $this->connection->alive($dsn);
            } catch (NotConnectedException $e) {
                $completed++;
                $this->logger->warn(sprintf('%s Attempt no. %d', $e->getMessage(), $completed));
                sleep($this->every);

                if ($this->toBeOrNotToBe($completed)) {
                    $this->logger->info('Exit from application because connection not established.');
                    exit(1);
                }

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
     * Exit from application if connection does not successful or not.
     *
     * @param $completed
     * @return bool
     */
    private function toBeOrNotToBe($completed): bool
    {
        return $completed === $this->attempt && !static::$disableExit;
    }
}
