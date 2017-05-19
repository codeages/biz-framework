<?php

use Doctrine\Common\Annotations\AnnotationRegistry;
use Codeages\Biz\Framework\Context\Biz;
use Codeages\Biz\Framework\Provider\DoctrineServiceProvider;
use Codeages\Biz\Framework\Provider\TargetlogServiceProvider;
use Codeages\Biz\Framework\UnitTests\UnitTestsBootstrap;
use Tests\IntegrationTestCase;

define('ROOT_DIR', dirname(__DIR__));

$loader = require ROOT_DIR.'/vendor/autoload.php';

AnnotationRegistry::registerLoader(array($loader, 'loadClass'));
IntegrationTestCase::$classLoader = $loader;

echo "[exec] bin/phpmig migrate\n";
chdir(__DIR__);
passthru('bin/phpmig migrate');
