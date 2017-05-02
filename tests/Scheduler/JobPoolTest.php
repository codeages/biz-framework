<?php

namespace Tests;

use Codeages\Biz\Framework\Provider\SchedulerServiceProvider;
use Codeages\Biz\Framework\Scheduler\Job\AbstractJob;
use PHPUnit\Framework\TestCase;

class JobPoolTest extends TestCase
{
    const NOT_EXIST_ID = 9999;

    public function __construct()
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
            'redis.options' => array(
                'host' => array('127.0.0.1:6379'),
            ),
        );
        $biz = new Biz($config);
        $biz['autoload.aliases']['TestProject'] = 'TestProject\Biz';
        $biz->register(new DoctrineServiceProvider());
        $biz->register(new RedisServiceProvider());
        $biz->register(new SchedulerServiceProvider());
        $biz->boot();

        $this->biz = $biz;
    }

    public function setUp()
    {
        $this->biz['db']->exec('DROP TABLE IF EXISTS `example`');
        $this->biz['db']->exec("
            CREATE TABLE `job_pool` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
              `group` varchar(1024) NOT NULL DEFAULT 'default' COMMENT '组名',
              `maxNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最大数',
              `num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '已使用的数量',
              `timeOut` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '执行超时时间',
              `updatedTime` int(10) unsigned NOT NULL COMMENT '更新时间',
              `createdTime` int(10) unsigned NOT NULL COMMENT '创建时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
    }

    public function testRun()
    {

    }
}

class ExampleJob extends AbstractJob
{
    public function execute()
    {
        print_r('test job.');
    }
}