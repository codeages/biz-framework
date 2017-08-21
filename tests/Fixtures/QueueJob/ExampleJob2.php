<?php
namespace Tests\Fixtures\QueueJob;

use Codeages\Biz\Framework\Queue\AbstractJob;

class ExampleJob2 extends AbstractJob
{
    public function execute()
    {
        echo "example job 2.";
    }
}