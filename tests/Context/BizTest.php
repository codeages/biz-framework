<?php

namespace Tests;

use Codeages\Biz\Framework\Context\Biz;
use Codeages\Biz\Framework\Provider\MonologServiceProvider;
use PHPUnit\Framework\TestCase;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class BizTest extends TestCase
{
    public function testConstruct()
    {
        $biz = new Biz();
        $this->assertInstanceOf('Codeages\Biz\Framework\Context\Biz', $biz);

        $config = [
            'debug' => true,
            'migration.directories' => ['migrations'],
        ];
        $biz = new Biz($config);
        $this->assertEquals($config['debug'], $biz['debug']);
        $this->assertEquals($config['migration.directories'], $biz['migration.directories']);
    }

    public function testRegister()
    {
        $biz = new Biz();
        $biz->register(new BizTestServiceProvider1(), [
            'test_1.options' => [
                'option1' => 'option1_value',
                'option2' => 'option2',
            ],
        ]);

        $this->assertEquals('test_1', $biz['test_1']);
        $this->assertEquals('option1_value', $biz['test_1.options']['option1']);

        $biz->register(new MonologServiceProvider());
    }

    public function testBoot()
    {
        $biz = new Biz();
        $biz->boot();
        $biz->boot();
        $this->assertTrue(true);
    }

    public function testRegisterAutoloadAlias()
    {
        $biz = new Biz();
        $biz['autoload.aliases'][''] = 'Biz';
        $biz['autoload.aliases']['Example'] = 'Example';
        $this->assertEquals(2, count($biz['autoload.aliases']));
    }

    public function testServiceWithProxy()
    {
        $biz = new Biz([
            'service_proxy_enabled' => true,
        ]);
        $biz['autoload.aliases']['Example'] = 'Tests\Example';
        $service = $biz->service('Example:ExampleService');
        $this->assertInstanceOf('Tests\Example\Service\ExampleService', $service->getClass());
        $this->assertEquals($service, $biz['@Example:ExampleService']);

        $biz = new Biz();
        $biz['autoload.aliases']['Example'] = 'Tests\\Example';
        $service1 = $biz->service('Example:ExampleService');
        $service2 = $biz->service('Example:ExampleService');
        $this->assertEquals($service1, $service2);
    }

    public function testServiceWithoutProxy()
    {
        $biz = new Biz();
        $biz['autoload.aliases']['Example'] = 'Tests\Example';
        $service = $biz->service('Example:ExampleService');
        $this->assertInstanceOf('Tests\Example\Service\ExampleService', $service);
        $this->assertEquals($service, $biz['@Example:ExampleService']);

        $biz = new Biz();
        $biz['autoload.aliases']['Example'] = 'Tests\\Example';
        $service1 = $biz->service('Example:ExampleService');
        $service2 = $biz->service('Example:ExampleService');
        $this->assertEquals($service1, $service2);
    }

    public function testDao()
    {
        $biz = new Biz([
            'debug' => true,
        ]);
        $biz['autoload.aliases']['Example'] = 'Tests\\Example';
        $dao = $biz->dao('Example:ExampleDao');
        $this->assertEquals($dao, $biz['@Example:ExampleDao']);

        $biz = new Biz([
            'debug' => true,
        ]);
        $biz['autoload.aliases']['Example'] = 'Tests\\Example';
        $dao1 = $biz->dao('Example:ExampleDao');
        $dao2 = $biz->dao('Example:ExampleDao');
        $this->assertEquals($dao1, $dao2);
    }
}

class BizTestServiceProvider1 implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container['test_1'] = 'test_1';
    }
}
