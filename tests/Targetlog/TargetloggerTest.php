<?php

namespace Tests\Targetlog;

use Codeages\Biz\Framework\Targetlog\Service\TargetlogService;
use Codeages\Biz\Framework\Targetlog\Targetlogger;
use Tests\IntegrationTestCase;

class TargetloggerTest extends IntegrationTestCase
{
    public function testDebug()
    {
        $logger = $this->createLogger('example', 1);
        $log =  $logger->debug('hello world.', array(
            '@action' => 'test',
            '@user_id' => 1,
            '@ip' => '127.0.0.1',
            'test_key' => 'test_value',
        ));

        $log = $this->getTargetlogService()->getLog($log['id']);
        $this->assertEquals(TargetlogService::DEBUG, $log['level']);
        $this->assertEquals('example', $log['target_type']);
        $this->assertEquals(1, $log['target_id']);
        $this->assertEquals('hello world.', $log['message']);
        $this->assertEquals('test', $log['action']);
        $this->assertEquals(1, $log['user_id']);
        $this->assertEquals('127.0.0.1', $log['ip']);
        $this->assertEquals('test_value', $log['context']['test_key']);
    }

    protected function createLogger($targetType, $targetId)
    {
        return new Targetlogger($this->biz, $targetType, $targetId);
    }

    /**
     * @return TargetlogService
     */
    protected function getTargetlogService()
    {
        return $this->biz->service('Targetlog:TargetlogService');
    }
}
