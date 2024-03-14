<?php

namespace Tests\Provider;

use Codeages\Biz\Framework\Provider\MonologServiceProvider;
use PHPUnit\Framework\TestCase;
use Pimple\Container;

class MonologServiceProviderTest extends TestCase
{
    public function testRegister()
    {
        $container = new Container([
            'debug' => true,
        ]);
        $provider = new MonologServiceProvider();
        $provider->register($container);

        $logger = $container['logger'];

        $this->assertInstanceOf('\Monolog\Logger', $logger);
    }
}
