<?php

namespace Tests;

use Codeages\Biz\Framework\Scheduler\Job\DeleteFiredLogJob;

class DeleteFiredLogJobTest extends IntegrationTestCase
{
    public function testExecute()
    {
        $schedulerService = $this->mockObjectIntoBiz(
            'Scheduler:SchedulerService',
            array(
                array(
                    'functionName' => 'deleteUnaccquiredJobFired',
                    'withParams' => array(15),
                ),
            )
        );

        $job = new DeleteFiredLogJob(array(), $this->biz);
        $job->execute();

        $schedulerService->shouldHaveReceived('deleteUnaccquiredJobFired')->times(1);
        $this->assertTrue(true);
    }
}
