<?php declare(strict_types=1);

namespace Roquie\Database\Connection\Close;

use PDO;

class ClosePdoConnection implements CloseConnectionInterface
{
    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * ClosePdoConnection constructor.
     *
     * @param \PDO $pdo
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Close active database connection;
     *
     * @return void
     */
    public function close(): void
    {
        $this->pdo = null;
    }
}
