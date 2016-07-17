<?php

namespace Codeages\Biz\Framework\Tests\Example;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ExampleServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container['example'] = function () {
            return new Example();
        };

    }
}

class Example
{

}