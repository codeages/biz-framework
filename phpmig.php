<?php

use Codeages\Biz\Framework\Context\Biz;
use Codeages\Biz\Framework\Provider\DoctrineServiceProvider;
use Codeages\Biz\Framework\Provider\TargetlogServiceProvider;
use Codeages\Biz\Framework\Dao\MigrationBootstrap;

define('ROOT_DIR', dirname(__DIR__));

$config = array(
    'db.options' => array(
        'dbname' => getenv('DB_NAME') ? : 'biz-framework',
        'user' => getenv('DB_USER') ? : 'root',
        'password' => getenv('DB_PASSWORD') ? : '',
        'host' => getenv('DB_HOST') ? : '127.0.0.1',
        'port' => getenv('DB_PORT') ? : 3306,
        'driver' => 'pdo_mysql',
        'charset' => 'utf8',
    ),
);

$biz = new Biz($config);
$biz->register(new DoctrineServiceProvider());
$biz->register(new TargetlogServiceProvider());
$biz->boot();

$migration = new MigrationBootstrap($biz['db'], $biz['migration.directories']);

return $migration->boot();
