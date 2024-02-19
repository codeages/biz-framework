<?php

namespace Tests;

use Codeages\Biz\Framework\Util\TimeMachine;

class SchedulerTest extends IntegrationTestCase
{
    /**
     * @expectedException \Exception
     */
    public function testCreateJobWithoutName()
    {
        $job = [
            'source' => 'MAIN',
            'class' => 'Tests\\Example\\Job\\ExampleJob',
            'expression' => '0 17 * * *',
            'args' => ['courseId' => 1],
            'priority' => 100,
            'misfire_threshold' => 3000,
            'misfire_policy' => 'missed',
        ];

        $this->getSchedulerService()->register($job);
    }

    /**
     * @expectedException \Exception
     */
    public function testCreateJobWithoutExpression()
    {
        $job = [
            'name' => 'test',
            'source' => 'MAIN',
            'class' => 'Tests\\Example\\Job\\ExampleJob',
            'args' => ['courseId' => 1],
            'priority' => 100,
            'misfire_threshold' => 3000,
            'misfire_policy' => 'missed',
        ];

        $this->getSchedulerService()->register($job);
    }

    /**
     * @expectedException \Exception
     */
    public function testCreateJobWithoutClass()
    {
        $job = [
            'name' => 'test',
            'expression' => '0 17 * * *',
            'source' => 'MAIN',
            'args' => ['courseId' => 1],
            'priority' => 100,
            'misfire_threshold' => 3000,
            'misfire_policy' => 'missed',
        ];

        $this->getSchedulerService()->register($job);
    }

    public function testDeleteUnacquiredJobFired()
    {
        TimeMachine::setMockedTime(1521598571);
        $jobFiredDao = $this->mockObjectIntoBiz(
            'Scheduler:JobFiredDao',
            [
                [
                    'functionName' => 'deleteUnacquiredBeforeCreatedTime',
                    'withParams' => [1521512171], //1521598571-60*60*24, 1天前
                    'returnValue' => 1,
                ],
            ]
        );

        $result = $this->getSchedulerService()->deleteUnacquiredJobFired(1);
        $jobFiredDao->shouldHaveReceived('deleteUnacquiredBeforeCreatedTime')->times(1);
        $this->assertEquals(1, $result);
    }

    public function testCreateJob()
    {
        $job = [
            'name' => 'test',
            'source' => 'MAIN',
            'expression' => '0 17 * * *',
//            'nextFireTime' => time()-1,
            'class' => 'Tests\\Example\\Job\\ExampleJob',
            'args' => ['courseId' => 1],
            'priority' => 100,
            'misfire_threshold' => 3000,
            'misfire_policy' => 'missed',
        ];

        $savedJob = $this->getSchedulerService()->register($job);

        $this->asserts($job, $savedJob);
        $this->assertNotEmpty($savedJob['next_fire_time']);

        $logs = $this->getSchedulerService()->searchJobLogs([], [], 0, 1);

        $excepted = [
            'name' => 'test',
            'source' => 'MAIN',
            'class' => 'Tests\\Example\\Job\\ExampleJob',
            'args' => ['courseId' => 1],
            'status' => 'created',
        ];
        foreach ($logs as $log) {
            $this->asserts($excepted, $log);
        }
    }

    public function testAfterNowRun()
    {
        $this->testCreateJob();
        $this->getSchedulerService()->execute();

        $second = time() % 60;
        if ($second > 57) {
            sleep(5);
        }

        $time = time() + 2;

        $job = [
            'name' => 'test2',
            'source' => 'MAIN',
            'expression' => $time,
            'class' => 'Tests\\Example\\Job\\ExampleJob',
            'args' => ['courseId' => 1],
            'priority' => 100,
            'misfire_threshold' => 3000,
            'misfire_policy' => 'executing',
        ];

        $savedJob = $this->getSchedulerService()->register($job);
        $this->getSchedulerService()->execute();
        $this->assertEquals($time - $time % 60, $savedJob['next_fire_time']);

        $this->asserts($job, $savedJob);

        $jobFireds = $this->getSchedulerService()->findJobFiredsByJobId($savedJob['id']);
        $this->assertNotEmpty($jobFireds[0]);

        $jobFired = $jobFireds[0];
        $this->assertEquals('success', $jobFired['status']);

        $savedJob = $this->getJobDao()->get($savedJob['id']);
        $this->assertEmpty($savedJob);
    }

    public function testBeforeNowRun()
    {
        $time = time() - 50000;

        $job = [
            'name' => 'test2',
            'source' => 'MAIN',
            'expression' => $time,
            'class' => 'Tests\\Example\\Job\\ExampleJob',
            'args' => ['courseId' => 1],
            'priority' => 100,
            'misfire_threshold' => 3000,
            'misfire_policy' => 'executing',
        ];

        $savedJob = $this->getSchedulerService()->register($job);
        $this->getSchedulerService()->execute();
        $this->assertEquals($time - $time % 60, $savedJob['next_fire_time']);

        $this->asserts($job, $savedJob);

        $jobFireds = $this->getSchedulerService()->findJobFiredsByJobId($savedJob['id']);
        $this->assertNotEmpty($jobFireds[0]);

        $jobFired = $jobFireds[0];
        $this->assertEquals('success', $jobFired['status']);

        $savedJob = $this->getJobDao()->get($savedJob['id']);
        $this->assertEmpty($savedJob);
    }

    public function testDeleteJobByName()
    {
        $job = [
            'name' => 'test',
            'source' => 'MAIN',
            'expression' => '0 17 * * *',
//            'nextFireTime' => time()-1,
            'class' => 'Tests\\Example\\Job\\ExampleJob',
            'args' => ['courseId' => 1],
            'priority' => 100,
            'misfire_threshold' => 3000,
            'misfire_policy' => 'missed',
        ];

        $savedJob = $this->getSchedulerService()->register($job);
        $this->getSchedulerService()->deleteJobByName('test');
        $savedJob = $this->getJobDao()->get($savedJob['id']);

        $this->assertEmpty($savedJob);
    }

    public function testFailJobResult()
    {
        $job = [
            'name' => 'test',
            'source' => 'MAIN',
            'expression' => time() - 2,
//            'nextFireTime' => time()-1,
            'class' => 'Tests\\Example\\Job\\ExampleFailJob',
            'args' => ['courseId' => 1],
            'priority' => 100,
            'misfire_threshold' => 3000,
            'misfire_policy' => 'missed',
        ];

        $job = $this->getSchedulerService()->register($job);
        $this->getSchedulerService()->execute();
        $savedJob = $this->getJobDao()->get($job['id']);
        $this->assertEmpty($savedJob);

        $jobFireds = $this->getSchedulerService()->findJobFiredsByJobId($job['id']);
        $this->assertEquals('failure', $jobFireds[0]['status']);
    }

    public function testAcquiredJobResult()
    {
        $job = [
            'name' => 'test',
            'source' => 'MAIN',
            'expression' => time() - 2,
//            'nextFireTime' => time()-1,
            'class' => 'Tests\\Example\\Job\\ExampleAcquiredJob',
            'args' => ['courseId' => 1],
            'priority' => 100,
            'misfire_threshold' => 3000,
            'misfire_policy' => 'missed',
        ];
        $job = $this->getSchedulerService()->register($job);
        $this->getSchedulerService()->execute();

        $savedJob = $this->getJobDao()->get($job['id']);
        $this->assertEmpty($savedJob);

        $jobFireds = $this->getSchedulerService()->findJobFiredsByJobId($job['id']);
        $this->assertEquals('failure', $jobFireds[0]['status']);
    }

    public function testTimeoutJobs()
    {
        $job = [
            'name' => 'test',
            'source' => 'MAIN',
            'expression' => time() - 2,
//            'nextFireTime' => time()-1,
            'class' => 'Tests\\Example\\Job\\ExampleAcquiredJob',
            'args' => ['courseId' => 1],
            'priority' => 100,
            'misfire_threshold' => 3000,
            'misfire_policy' => 'missed',
        ];
        $job = $this->getSchedulerService()->register($job);
        $this->getSchedulerService()->execute();
        $this->mockUnReleasePool($job);

        $options = $this->biz['scheduler.options'];
        $options['timeout'] = 1;
        $this->biz['scheduler.options'] = $options;

        $this->getSchedulerService()->markTimeoutJobs();
        $savedJob = $this->getJobDao()->get($job['id']);
        $this->assertEmpty($savedJob);

        $jobFireds = $this->getSchedulerService()->findJobFiredsByJobId($job['id']);
        $this->assertEquals('timeout', $jobFireds[0]['status']);
    }

    public function testCreateErrorLog()
    {
        $job = [
            'id' => 22,
            'name' => 'test',
            'source' => 'MAIN',
            'expression' => '0 17 * * *',
            'class' => 'Tests\\Example\\Job\\ExampleJob',
            'args' => ['courseId' => 1],
            'priority' => 100,
            'misfire_threshold' => 3000,
            'misfire_policy' => 'missed',
        ];
        $this->getSchedulerService()->createErrorLog(['job_detail' => $job], 'error', 'error');
        $result = $this->getJobLogDao()->search(['job_fired_id' => 0], ['created_time' => 'DESC'], 0, PHP_INT_MAX);
        $this->assertEquals('error', $result[0]['message']);
    }

    protected function wavePoolNum($id, $diff)
    {
        $ids = [$id];
        $diff = ['num' => $diff];
        $this->getJobPoolDao()->wave($ids, $diff);
    }

    protected function getJobPoolDao()
    {
        return $this->biz->dao('Scheduler:JobPoolDao');
    }

    protected function asserts($excepted, $acturel)
    {
        $keys = array_keys($excepted);
        foreach ($keys as $key) {
            if ('expression' == $key) {
                continue;
            }
            $this->assertEquals($excepted[$key], $acturel[$key]);
        }
    }

    protected function getJobDao()
    {
        return $this->biz->dao('Scheduler:JobDao');
    }

    protected function getJobFiredDao()
    {
        return $this->biz->dao('Scheduler:JobFiredDao');
    }

    protected function getJobLogDao()
    {
        return $this->biz->dao('Scheduler:JobLogDao');
    }

    protected function getSchedulerService()
    {
        return $this->biz->service('Scheduler:SchedulerService');
    }

    /**
     * @param $job
     */
    protected function mockUnReleasePool($job)
    {
        $this->getJobFiredDao()->update(['job_id' => $job['id']], [
            'status' => 'executing',
            'fired_time' => time() - 2,
        ]);

        $jobPool = $this->getJobPoolDao()->getByName($job['pool']);
        $this->wavePoolNum($jobPool['id'], 1);
    }
}
