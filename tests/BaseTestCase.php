<?php
namespace Tests;

use PHPUnit\Framework\TestCase;
use Codeages\Biz\Framework\Context\Biz;
use Codeages\Biz\Framework\Provider\DoctrineServiceProvider;


class BaseTestCase extends TestCase
{
    /**
     * @var \Composer\Autoload\ClassLoader
     */
    public static $classLoader = null;

    protected function createBiz()
    {
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
        $biz->boot();

        return $biz;
    }

    protected function createRedis()
    {
        $redis = new \Redis();
        $redis->connect(getenv('REDIS_HOST'), getenv('REDIS_PORT'));
        $redis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP);
        return $redis;
    }
}