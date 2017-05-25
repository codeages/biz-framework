<?php

namespace Tests;

use Codeages\Biz\Framework\UnitTests\BaseTestCase;
use Cron\CronExpression;

class CronExpressionTest extends BaseTestCase
{
    public function testCronExpression()
    {
        $expressions =  array(
            '0 17 * * *',
            '0 17 * * * 2017',
            '30 17 12 12 * 2016',
            '0 17 12 12 * 2017',
            '* 12 * * *'
        );

        foreach ($expressions as $expression) {
            $this->assertTrue(CronExpression::isValidExpression($expression));
        }
    }
}