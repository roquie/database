<?php declare(strict_types=1);

namespace Roquie\Database\Connection\Close;

interface CloseConnectionInterface
{
    /**
     * Close active database connection;
     *
     * @return void
     */
    public function close(): void;
}
