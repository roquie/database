<?php

use Roquie\Database\Connection\Wait\Wait;
use Roquie\Database\Migration\Migrate;
use Roquie\Database\Seed\Seed;

require_once __DIR__ . '/../../vendor/autoload.php';

$dsn = 'pgsql:dbname=postgres;host=localhost;user=postgres;password=postgres';

Wait::connection($dsn, 5, function (PDO $pdo) {
    Migrate::new($pdo)
           ->install()
           ->run();

    Seed::new($pdo)
        ->run(Seed::DEFAULT_SEED);
});
