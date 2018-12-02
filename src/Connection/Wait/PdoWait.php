<?php declare(strict_types=1);

namespace Roquie\Database\Connection\Wait;

use PDO;
use PDOException;
use Roquie\Database\Connection\Exception\NotConnectedException;

class PdoWait implements WaitInterface
{
    /**
     * @param string $dsn
     * @return mixed
     * @throws \Roquie\Database\Connection\Exception\NotConnectedException
     */
    public function alive(string $dsn)
    {
        try {
            $pdo = new PDO($dsn, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        } catch (PDOException $e) {
            throw NotConnectedException::forNotConnected($dsn);
        }

        return $pdo;
    }
}
