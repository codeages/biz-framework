<?php
namespace Codeages\Biz\Framework\Queue;

class Worker
{
    public function run($queue)
    {
        $job = $this->getQueueService()->getNextJob($queue);

        


        

    }

    protected function getQueueService()
    {

    }
}