<?php declare(strict_types=1);

namespace Roquie\Database\Connection\Wait;

interface WaitInterface
{
    /**
     * @param string $dsn
     * @return mixed
     * @throws \Roquie\Database\Connection\Exception\NotConnectedException
     */
    public function alive(string $dsn);
}
