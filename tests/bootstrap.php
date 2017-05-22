<?php

use Doctrine\Common\Annotations\AnnotationRegistry;
use Tests\IntegrationTestCase;

define('ROOT_DIR', dirname(__DIR__));

$loader = require ROOT_DIR.'/vendor/autoload.php';

AnnotationRegistry::registerLoader(array($loader, 'loadClass'));
IntegrationTestCase::$classLoader = $loader;

echo "[exec] bin/phpmig migrate\n";
chdir(__DIR__);
passthru('bin/phpmig migrate');

$dns = sprintf('mysql:dbname=%s;host=%s', getenv('DB_NAME'), getenv('DB_HOST'));

$pdo = new PDO($dns, getenv('DB_USER'), getenv('DB_PASSWORD'));
$pdo->exec(\Tests\Example\Fixtures\Loader::loadSql());
unset($pdo);
