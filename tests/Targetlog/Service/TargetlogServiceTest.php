<?php

namespace Tests\Targetlog\Service;

use Codeages\Biz\Framework\UnitTests\BaseTestCase;
use Codeages\Biz\Framework\TargetLog\Service\TargetlogService;

class TargetlogServiceTest extends BaseTestCase
{
    public function testAddLog()
    {
        $created = $this->getTargetlogService()->log(TargetlogService::INFO, 'example', 1, 'hello world.');
        $this->assertEquals(TargetlogService::INFO, $created['level']);
        $this->assertEquals('example', $created['target_type']);
        $this->assertEquals(1, $created['target_id']);
    }

    public function testGetLog()
    {
        $created = $this->getTargetlogService()->log(TargetlogService::INFO, 'example', 1, 'hello world.');
        $found = $this->getTargetlogService()->getLog($created['id']);
        $this->assertEquals($created['id'], $found['id']);
    }

    protected function getTargetlogService()
    {
        return self::$biz->service('Targetlog:TargetlogService');
    }
}
