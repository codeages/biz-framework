<?php

namespace Codeages\Biz\Framework\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class SessionServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        $container['migration.directories'][] = dirname(dirname(__DIR__)).'/migrations/session';
        $container['autoload.aliases']['Session'] = 'Codeages\Biz\Framework\Session';
    }
}