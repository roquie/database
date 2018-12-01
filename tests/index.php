<?php

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Roquie\Database\Migrations\Creator;
use Roquie\Database\Migrations\Migrate;
use Roquie\Database\Migrations\Notify\NotifyInterface;

require_once __DIR__ . '/../vendor/autoload.php';

//$pdo = new PDO('pgsql:dbname=automigrator_tests;host=localhost', 'roquie', '');
//$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$adapter    = new Local(__DIR__ . '/migrations');
$filesystem = new Filesystem($adapter);

$dsn = 'pgsql:dbname=automigrator_tests;host=localhost;user=roquie';
$migrator = Migrate::new($dsn, Migrate::DEFAULT_PATH, NotifyInterface::STDOUT);
$migrator
    ->install()
    ->run()
//    ->drop()
;

//Creator::new()->create('test', 'tests');

