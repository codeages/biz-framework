<?php

namespace Tests;

use Codeages\Biz\Framework\Context\Biz;
use Codeages\Biz\Framework\Provider\RedisServiceProvider;
use Codeages\Biz\Framework\Provider\SchedulerServiceProvider;
use Codeages\Biz\Framework\Util\ArrayToolkit;
use PHPUnit\Framework\TestCase;

class SchedulerServiceTest extends TestCase
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
        $biz->register(new \Codeages\Biz\Framework\Provider\DoctrineServiceProvider());
        $biz->register(new RedisServiceProvider());
        $biz->register(new SchedulerServiceProvider());
        $biz->boot();

        $this->biz = $biz;
    }

    public function testCreateJob()
    {
        $jobDetail = array(
            'name' => 'test',
            'pool' => 'test',
            'source' => 'MAIN',
            'expression' => '0 0 12 * * ?',
            'class' => 'TestProject\\Biz\\Example\\Job\\ExampleJob',
            'data' => array('courseId'=>1),
            'priority' => 100,
            'misfireThreshold' => 30,
            'misfirePolicy' => 'miss',
        );

        $savedJobDetail = $this->getSchedulerService()->create($jobDetail);

        $this->asserts($jobDetail, $savedJobDetail);

        $logs = $this->getJobLogService()->search(array(), array(), 0, 1);

        $excepted = array(
            'name' => 'test',
            'pool' => 'test',
            'source' => 'MAIN',
            'class' => 'TestProject\\Biz\\Example\\Job\\ExampleJob',
            'data' => array('courseId'=>1),
            'status' => 'created',
        );
        foreach ($logs as $log) {
            $this->asserts($excepted, $log);
        }
    }

    public function testRun()
    {
        $this->testCreateJob();
        $this->getSchedulerService()->run();
    }

    protected function asserts($excepted, $acturel)
    {
        $keys = array_keys($excepted);
        foreach ($keys as $key) {
            $this->assertEquals($excepted[$key], $acturel[$key]);
        }
    }

    protected function getJobLogService()
    {
        return $this->biz->service('Scheduler:JobLogService');
    }

    public function getSchedulerService()
    {
        return $this->biz->service('Scheduler:SchedulerService');
    }
}