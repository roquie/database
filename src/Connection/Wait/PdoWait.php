<?php declare(strict_types=1);

namespace Roquie\Database\Connection\Wait;

use PDO;
use PDOException;
use Roquie\Database\Connection\Exception\NotConnectedException;

class PdoWait implements WaitInterface
{
    /**
     * @param string $dsn
     * @param array $options
     * @return mixed
     * @throws \Roquie\Database\Connection\Exception\NotConnectedException
     */
    public function alive(string $dsn, array $options = [])
    {
        try {
            $pdo = new PDO($dsn, null, null, $this->opts($options));
        } catch (PDOException $e) {
            throw NotConnectedException::forNotConnected($dsn);
        }

        return $pdo;
    }

    /**
     * @param array $options
     * @return array
     */
    private function opts(array $options)
    {
        return [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION] + $options;
    }
}
