<?php

namespace Tests;

use Codeages\Biz\Framework\Context\Biz;
use Codeages\Biz\Framework\Provider\RedisServiceProvider;
use Codeages\Biz\Framework\Provider\SchedulerServiceProvider;
use Codeages\Biz\Framework\UnitTests\BaseTestCase;

class SchedulerServiceTest extends BaseTestCase
{
    public function testCreateJob()
    {
        $jobDetail = array(
            'name' => 'test',
            'pool' => 'test',
            'source' => 'MAIN',
            'expression' => '0 17 * * *',
            'class' => 'TestProject\\Biz\\Example\\Job\\ExampleJob',
            'data' => array('courseId'=>1),
            'priority' => 100,
            'misfireThreshold' => 30,
            'misfirePolicy' => 'miss',
        );

        $savedJobDetail = $this->getSchedulerService()->create($jobDetail);

        $this->asserts($jobDetail, $savedJobDetail);
        $this->assertNotEmpty($savedJobDetail['nextFireTime']);

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
        return self::$biz->service('Scheduler:JobLogService');
    }

    public function getSchedulerService()
    {
        return self::$biz->service('Scheduler:SchedulerService');
    }
}