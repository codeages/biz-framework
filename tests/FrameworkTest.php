<?php

namespace Codeages\Biz\Framework\Tests;

use Codeages\Biz\Framework\Tests\Example\ExampleKernel;
use Codeages\Biz\Framework\Tests\Example\ExampleServiceProvider;


class FrameworkTest extends \PHPUnit_Framework_TestCase
{
    public function testRegister()
    {
        $this->kernel = new ExampleKernel();
        $this->kernel->register(new ExampleServiceProvider());
        $this->assertInstanceOf('Codeages\Biz\Framework\Tests\Example\Example', $this->kernel['example']);
    }
}
