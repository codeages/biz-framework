<?php

/*
 * 此文件来自 Silex 项目(https://github.com/silexphp/Silex).
 *
 * 版权信息请看 LICENSE.SILEX
 */

namespace Codeages\Biz\Framework\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Codeages\Biz\Framework\Dao\DaoProxy\CacheDaoProxy;
use Codeages\Biz\Framework\Dao\CacheStrategy\TableCacheStrategy;
use Codeages\Biz\Framework\Dao\CacheStrategy\PromiseCacheStrategy;

class CacheServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container['cache.options'] = array(
            array(
                'host' => '127.0.0.1',
                'port' => 6379,
                'timeout' => 1,
                'reserved' => null,
                'retry_interval' => 100,
            )
        );

        $container['autoload.object_maker.dao'] = function ($container) {
            return function ($namespace, $name) use ($container) {
                $class = "{$namespace}\\Dao\\Impl\\{$name}Impl";
                return new CacheDaoProxy($container, new $class($container), $container['dao.serializer']);
            };
        };

        $container['dao.cache.enabled'] = true;
        $container['dao.cache.double.enabled'] = true;

        $container['dao.cache.double.first'] = function () {
            return new MemoryCacheStrategy();
        };

        $container['cache.dao.double'] = $container->factory(function ($container) {
            return new DoubleCacheStrategy();
        });

        $container['cache.dao.strategy.table'] = function ($container) {
            return new TableCacheStrategy($container);
        };

        $container['cache.dao.strategy.promise'] = function ($container) {
            return new PromiseCacheStrategy($container);
        };
    }
}
