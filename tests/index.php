<?php

use Illuminate\Container\Container;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Roquie\Database\Migration\Creator;
use Roquie\Database\Migration\Migrate;
use Roquie\Database\Notify\NotifyInterface;
use Roquie\Database\Migration\Whois;
use Roquie\Database\Seed\Seed;

require_once __DIR__ . '/../vendor/autoload.php';

//$pdo = new PDO('pgsql:dbname=automigrator_tests;host=localhost', 'roquie', '');
//$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$adapter    = new Local(__DIR__ . '/migrations');
$filesystem = new Filesystem($adapter);

$dsn = 'pgsql:dbname=automigrator_tests;host=localhost;user=roquie';
//$migrator = Migrate::new($dsn, Migrate::DEFAULT_PATH, NotifyInterface::STDOUT);
//$migrator
//    ->install()
//    ->run()
//    ->drop()
;

//Creator::new()->create('test', 'tests');

//\Roquie\Database\Seed\Creator::new()->create('fest-aa_Ds');

$container = new Container();
$container->bind(Whois::class, function () {
    return new Whois();
});

$seed = Seed::new($dsn);
$seed->setContainer($container);
$seed->run();
