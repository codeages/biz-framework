<?php

namespace Codeages\Biz\Framework\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class SessionServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container['autoload.aliases']['Session'] = 'Codeages\Biz\Framework\Session';

        $container['session.options'] = array(
            'max_life_time' => 7200,
            'redis_storage' => false,
            'sess_prefix' => 'biz_session_'
        );

        $container['console.commands'][] = function () use ($container) {
            return new \Codeages\Biz\Framework\Session\Command\TableCommand($container);
        };

    }
}