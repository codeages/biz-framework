<?php

namespace Codeages\Biz\Framework\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class SessionServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container['autoload.aliases']['Session'] = 'Codeages\Biz\Framework\Session';

        $container['console.commands'][] = function () use ($container) {
            return new \Codeages\Biz\Framework\Session\Command\TableCommand($container);
        };

    }
}