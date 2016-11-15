<?php

use Codeages\Biz\Framework\Context\Biz;
use Codeages\Biz\Framework\UnitTests\UnitTestsBootstrap;
use Codeages\Biz\Framework\Provider\DoctrineServiceProvider;

class UnitTestsBootstrapTest extends \PHPUnit_Framework_TestCase
{

    public function testBoot()
    {
        $config = array(
            'db.options' => array(
                'driver' => getenv('DB_DRIVER'),
                'dbname' => getenv('DB_NAME'),
                'host' => getenv('DB_HOST'),
                'user' => getenv('DB_USER'),
                'password' => getenv('DB_PASSWORD'),
                'charset' => getenv('DB_CHARSET'),
                'port' => getenv('DB_PORT'),
            ),
        );
        $biz = new Biz($config);
        $biz['migration.directories'][] = dirname(__DIR__) . '/TestProject/migrations';
        $biz->register(new DoctrineServiceProvider());
        $biz->boot();

        $bootstrap = new UnitTestsBootstrap($biz);
        $bootstrap->boot();
    }
}
