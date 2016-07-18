<?php

namespace Codeages\Biz\Framework\Tests\Example;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Codeages\Biz\Framework\Context\MigrationProviderInterface;
use Codeages\Biz\Framework\Context\Kernel;

class ExampleServiceProvider implements ServiceProviderInterface, MigrationProviderInterface
{
    public function register(Container $container)
    {
        $container['example'] = function () {
            return new Example();
        };

    }

    public function registerMigrationDirectory(Kernel $contaier)
    {
        $contaier->put('migration_directories', __DIR__ . '/database');
    }
}

class Example
{

}