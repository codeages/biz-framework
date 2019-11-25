<?php

namespace Tests\Utility;

use Codeages\Biz\Framework\Utility\MilliTime;
use PHPUnit\Framework\TestCase;

class MilliTimeTest extends TestCase
{
    public function testNow()
    {
        $now = time() * 1000;
        $this->assertGreaterThanOrEqual($now, MilliTime::now());
        $this->assertLessThan($now + 1000, MilliTime::now());
    }

    public function testFormat()
    {
        $now = MilliTime::now();
        $this->assertEquals(date('Y-m-d H:i:s', $now/1000), MilliTime::format('Y-m-d H:i:s', $now));
    }

    public function testToSecond()
    {
        $second = time();
        $now = MilliTime::now();
        $this->assertEquals($second, MilliTime::toSecond($now));
    }

    public function testFromSecond()
    {
        $second = time();
        $now = MilliTime::fromSecond($second);
        $this->assertEquals($second*1000, $now);
    }
}