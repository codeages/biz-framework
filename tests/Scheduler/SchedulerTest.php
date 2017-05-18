<?php

namespace Tests;

use Codeages\Biz\Framework\Context\Biz;
use Codeages\Biz\Framework\Provider\RedisServiceProvider;
use Codeages\Biz\Framework\Provider\SchedulerServiceProvider;
use Codeages\Biz\Framework\UnitTests\BaseTestCase;

class SchedulerTest extends BaseTestCase
{
    public function testCreateJob()
    {
        $job = array(
            'name' => 'test',
            'pool' => 'test',
            'source' => 'MAIN',
            'expression' => '0 17 * * *',
//            'nextFireTime' => time()-1,
            'class' => 'TestProject\\Biz\\Example\\Job\\ExampleJob',
            'args' => array('courseId'=>1),
            'priority' => 100,
            'misfireThreshold' => 3000,
            'misfirePolicy' => 'missed',
        );

        $savedJob = self::$biz['scheduler']->schedule($job);

        $this->asserts($job, $savedJob);
        $this->assertNotEmpty($savedJob['nextFireTime']);

        $logs = $this->getJobLogService()->search(array(), array(), 0, 1);

        $excepted = array(
            'name' => 'test',
            'pool' => 'test',
            'source' => 'MAIN',
            'class' => 'TestProject\\Biz\\Example\\Job\\ExampleJob',
            'args' => array('courseId'=>1),
            'status' => 'created',
        );
        foreach ($logs as $log) {
            $this->asserts($excepted, $log);
        }
    }

    public function testRun()
    {
        $this->testCreateJob();
        self::$biz['scheduler']->execute();

        $job = array(
            'name' => 'test2',
            'pool' => 'test2',
            'source' => 'MAIN',
            'nextFireTime' => time()-1,
            'class' => 'TestProject\\Biz\\Example\\Job\\ExampleJob',
            'args' => array('courseId'=>1),
            'priority' => 100,
            'misfireThreshold' => 3000,
            'misfirePolicy' => 'executing',
        );

        self::$biz['scheduler']->schedule($job);
        self::$biz['scheduler']->execute();
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