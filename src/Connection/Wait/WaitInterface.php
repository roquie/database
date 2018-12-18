<?php declare(strict_types=1);

namespace Roquie\Database\Connection\Wait;

interface WaitInterface
{
    /**
     * @param string $dsn
     * @param array $options
     * @return mixed
     */
    public function alive(string $dsn, array $options = []);
}
