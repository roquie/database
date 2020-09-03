New maintained version of this package can be found at: https://github.com/spacetab-io/rdb-php (partially compatible)
This version archived as is and no longer maintained.

------------------------

Rdb
===

```php
<?php

use Roquie\Database\Connection\Wait\Wait;
use Roquie\Database\Migration\Migrate;
use Roquie\Database\Seed\Seed;

Wait::connection($dsn, function (PDO $pdo) {
    Migrate::new($pdo)
        ->install()
        ->run();

    Seed::new($pdo)
        ->run();
});
```

Doc is coming soon.
