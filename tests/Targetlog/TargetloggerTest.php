<?php

namespace Tests\Targetlog;

use Codeages\Biz\Framework\UnitTests\BaseTestCase;
use Codeages\Biz\Framework\Targetlog\Targetlogger;

class TargetloggerTestCase extends BaseTestCase
{
    public function testDebug()
    {
        $logger = $this->createLogger('example', 1);
        $logger->debug('hello world.', array('action' => 'test', 'user_id' => 1, 'ip' => '127.0.0.1', 'test_key' => 'test_value'));
    }

    protected function createLogger($targetType, $targetId)
    {
        return new Targetlogger(self::$biz, $targetType, $targetId);
    }

    protected function getTargetlogService()
    {
        return self::$biz->service['Targetlog:TargetlogService'];
    }
}
