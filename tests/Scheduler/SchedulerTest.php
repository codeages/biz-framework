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
            'misfire_threshold' => 3000,
            'misfire_policy' => 'missed',
        );

        $savedJob = $this->getSchedulerService()->register($job);

        $this->asserts($job, $savedJob);
        $this->assertNotEmpty($savedJob['next_fire_time']);

        $logs = $this->getSchedulerService()->searchJobLogs(array(), array(), 0, 1);

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
        $this->getSchedulerService()->execute();

        $job = array(
            'name' => 'test2',
            'pool' => 'test2',
            'source' => 'MAIN',
            'next_fire_time' => time()-1,
            'class' => 'TestProject\\Biz\\Example\\Job\\ExampleJob',
            'args' => array('courseId'=>1),
            'priority' => 100,
            'misfire_threshold' => 3000,
            'misfire_policy' => 'executing',
        );

        $savedJob = $this->getSchedulerService()->register($job);
        $this->getSchedulerService()->execute();

        $this->asserts($job, $savedJob);

        $jobFireds = $this->getSchedulerService()->findJobFiredsByJobId($savedJob['id']);
        $this->assertNotEmpty($jobFireds[0]);

        $jobFired = $jobFireds[0];
        $this->assertEquals('success', $jobFired['status']);
    }

    public function testDeleteJobByName()
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
            'misfire_threshold' => 3000,
            'misfire_policy' => 'missed',
        );

        $savedJob = $this->getSchedulerService()->register($job);
        $this->getSchedulerService()->deleteJobByName('test');
        $savedJob = $this->getJobDao()->get($savedJob['id']);

        $this->assertEmpty($savedJob);
    }


    protected function asserts($excepted, $acturel)
    {
        $keys = array_keys($excepted);
        foreach ($keys as $key) {
            $this->assertEquals($excepted[$key], $acturel[$key]);
        }
    }

    protected function getJobDao()
    {
        return self::$biz->dao('Scheduler:JobDao');
    }

    protected function getSchedulerService()
    {
        return self::$biz->service('Scheduler:SchedulerService');
    }
}