<?php

namespace Codeages\Biz\Framework\Tests\Dao;

use Codeages\Biz\Framework\Tests\Example\ExampleKernel;
use Codeages\Biz\Framework\Dao\MigrationBootstrap;

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
        $booted = $bootstrap->run();

        $this->assertNotNull($booted);
    }

}