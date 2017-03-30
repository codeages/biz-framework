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
use Codeages\Biz\Framework\Context\BizException;

class CacheServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container)  
    {
        $options = $container['cache.options'] = array(
            'host' => '127.0.0.1:6379',
            'timeout' => 1,
            'reserved' => null,
            'retry_interval' => 100,
        );

        $container['cache.redis'] = function($container) {
            $options = $container['cache.options'];
            if (!is_array($options['host'])) {
                $options['host'] = array((string)$options['host']);
            }

            if (empty($options['host'])) {
                throw new BizException("Biz value `cache.options`['host'] is error.");
            }

            if (count($options['host']) == 1) {
                list($host, $port) = explode(':', $options['host']);
                $redis = new Redis();
                $redis->pconnect($host, $port, $options['timeout'], $options['reserved'], $options['retry_interval']);
            } else {
                $redis = new RedisArray($options['host']);
            }
            $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);

            return $redis;
        };

        $container['dao.cache.enabled'] = true;
        $container['dao.cache.double.enabled'] = true;

        $container['dao.cache.double.first'] = function () {
            return new MemoryCacheStrategy();
        };

        $container['dao.cache.double'] = $container->factory(function ($container) {
            return new DoubleCacheStrategy();
        });

        $container['dao.cache.strategy.table'] = function ($container) {
            return new TableCacheStrategy($container['cache.redis']);
        };

        $container['dao.cache.strategy.promise'] = function ($container) {
            return new PromiseCacheStrategy($container['cache.redis']);
        };
    }
}
