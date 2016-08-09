<?php

namespace Codeages\Biz\Framework\Tests\Dao;

use Codeages\Biz\Framework\Dao\MigrationBootstrap;
use Codeages\Biz\Framework\Tests\Example\ExampleKernel;

class MigrationBootstrapTest extends \PHPUnit_Framework_TestCase
{
    public function __construct()
    {
    }

    public function testRun()
    {
        $kernel = new ExampleKernel();
        $kernel->boot();

        $bootstrap = new MigrationBootstrap($kernel, dirname(__DIR__));
        $booted    = $bootstrap->boot();

        $this->assertNotNull($booted);

    }
}
