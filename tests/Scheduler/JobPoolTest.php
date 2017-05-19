<?php

namespace Tests;

use Codeages\Biz\Framework\Context\Biz;
use Codeages\Biz\Framework\Provider\DoctrineServiceProvider;
use Codeages\Biz\Framework\Provider\RedisServiceProvider;
use Codeages\Biz\Framework\Provider\SchedulerServiceProvider;
use PHPUnit\Framework\TestCase;
use TestProject\Biz\Example\Job\ExampleJob;

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

    public function testRun()
    {
        $job = new ExampleJob(array(
            'pool'=>'default'
        ));

        $this->biz['scheduler.job.pool']->execute($job);

        $poolDetail = $this->biz['scheduler.job.pool']->getJobPool('default');
        $this->assertEquals(0, $poolDetail['num']);
        $this->assertEquals(10, $poolDetail['max_num']);
        $this->assertEquals(120, $poolDetail['timeout']);
    }
}
