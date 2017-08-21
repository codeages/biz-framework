<?php

namespace Codeages\Biz\Framework\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Codeages\Biz\Framework\Queue\Driver\SyncQueue;

class QueueServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container['migration.directories'][] = dirname(dirname(__DIR__)).'/migrations/Queue';
        $container['autoload.aliases']['Queue'] = 'Codeages\Biz\Framework\Queue';

        $container['queue.connection.sync'] = function ($container) {
            return new SyncQueue($container);
        };
    }
}
