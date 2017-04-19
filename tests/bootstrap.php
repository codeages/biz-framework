<?php

use Codeages\Biz\Framework\Context\Biz;
use Codeages\Biz\Framework\Provider\DoctrineServiceProvider;
use Codeages\Biz\Framework\Provider\TargetlogServiceProvider;
use Codeages\Biz\Framework\UnitTests\UnitTestsBootstrap;

define('ROOT_DIR', dirname(__DIR__));

require_once ROOT_DIR.'/vendor/autoload.php';

$config = array(
    'db.options' => array(
        'dbname' => getenv('DB_NAME') ?: 'biz-target-test',
        'user' => getenv('DB_USER') ?: 'root',
        'password' => getenv('DB_PASSWORD') ?: '',
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'port' => getenv('DB_PORT') ?: 3306,
        'driver' => 'pdo_mysql',
        'charset' => 'utf8',
    ),
);

$biz = new Biz($config);
$biz->register(new DoctrineServiceProvider());
$biz->register(new TargetlogServiceProvider());
$biz->boot();

$bootstrap = new UnitTestsBootstrap($biz);
$bootstrap->boot();
