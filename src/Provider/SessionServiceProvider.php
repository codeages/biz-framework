<?php

namespace Codeages\Biz\Framework\Provider;

use Codeages\Biz\Framework\Session\Storage\Impl\DbSessionStorageImpl;
use Codeages\Biz\Framework\Session\Storage\Impl\RedisSessionStorageImpl;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class SessionServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container['autoload.aliases']['Session'] = 'Codeages\Biz\Framework\Session';

        $container['session.options'] = array(
            'max_life_time' => 7200,
            'session_storage' => 'db', // exapmle: db, redis
        );

        $container['session.storage.db'] = function () use ($container) {
            return new DbSessionStorageImpl($container);
        };

        $container['session.storage.redis'] = function () use ($container) {
            return new RedisSessionStorageImpl($container);
        };

        $container['console.commands'][] = function () use ($container) {
            return new \Codeages\Biz\Framework\Session\Command\TableCommand($container);
        };

    }
}