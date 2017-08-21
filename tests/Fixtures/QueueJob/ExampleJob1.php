<?php
namespace Tests\Fixtures\QueueJob;

use Codeages\Biz\Framework\Queue\AbstractJob;

class ExampleJob1 extends AbstractJob
{
    public function execute()
    {
        $body = $this->getBody();
        echo $body['name'];
    }
}