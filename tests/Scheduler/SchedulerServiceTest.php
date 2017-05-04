<?php

namespace Tests;

use Codeages\Biz\Framework\Context\Biz;
use Codeages\Biz\Framework\Provider\RedisServiceProvider;
use Codeages\Biz\Framework\Provider\SchedulerServiceProvider;
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
            'class' => '\\\\Tests\\\\ExampleJob',
            'data' => array('courseId'=>1),
            'misfireThreshold' => 30,
            'misfirePolicy' => 'miss',
        );

        $this->getSchedulerService()->create($jobDetail);
    }

    public function getSchedulerService()
    {
        return $this->biz->service('Scheduler:SchedulerService');
    }
}