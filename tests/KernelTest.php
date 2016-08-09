<?php

namespace Codeages\Biz\Framework\Tests;

use Codeages\Biz\Framework\Tests\Example\ExampleKernel;
use Codeages\Biz\Framework\Tests\Example\Dao\Impl\ExampleDaoImpl;

class KernelTest extends \PHPUnit_Framework_TestCase
{
    public function testRegister()
    {
        $kernel = new ExampleKernel();
        $kernel->boot();

        $this->assertInstanceOf('Codeages\Biz\Framework\Tests\Example\Example', $kernel['example']);
    }

    public function testPut()
    {
        $kernel = new ExampleKernel();

        $kernel->put('test1', 'test1_1');
        $kernel->put('test1', array('test1_2s'));

        $kernel->put('test2', array('test2_1' => 'test2_1 value', 'test2_2' => 'test2_2_value'));
        $kernel->put('test2', array('test2_3' => 'test2_3 value', 'test2_4' => 'test2_4_value'));
        $kernel->boot();
        $this->assertTrue(is_array($kernel['test1']));
        $this->assertEquals(2, count($kernel['test1']));

        $this->assertEquals(4, count($kernel['test2']));
    }

    public function testRegisterMigrationProvider()
    {
        $kernel = new ExampleKernel();
        $kernel->boot();

        $this->assertTrue(is_array($kernel['migration_directories']));
    }

    public function testDao()
    {
        $kernel = new ExampleKernel();
        $kernel->boot();

        $kernel['example.example_dao'] = $kernel->dao(function ($container) {
            return new ExampleDaoImpl($container);
        });

        $declares = $kernel->get('example.example_dao')->declares();

        $this->assertTrue(is_array($declares));
    }
}
