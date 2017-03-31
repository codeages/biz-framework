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
use Redis;
use RedisArray;

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
                $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
                $redis->pconnect($host, $port, $options['timeout'], $options['reserved'], $options['retry_interval']);
            } else {
                $redis = new RedisArray($options['host']);
                $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
            }

            return $redis;
        };

        $container['dao.cache.first.enabled'] = true;
        $container['dao.cache.second.enabled'] = true;

        $container['dao.cache.chain'] = $container->factory(function ($container) {
            return new DoubleCacheStrategy();
        });

        $container['dao.cache.first'] = function() {
            return new MemoryCacheStrategy();
        };

        $container['dao.cache.second.strategy.table'] = function ($container) {
            return new TableCacheStrategy($container['cache.redis']);
        };

        $container['dao.cache.second.strategy.promise'] = function ($container) {
            return new PromiseCacheStrategy($container['cache.redis']);
        };
    }
}
