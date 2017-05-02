<?php

namespace Codeages\Biz\Framework\Provider;

use Codeages\Biz\Framework\Scheduler\Pool\JobPool;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class SchedulerServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container['migration.directories'][] = dirname(dirname(__DIR__)).'/migrations/scheduler';
        $container['scheduler.job.pool.options'] = array(
            'maxNum'  => 10,
            'timeout' => 120,
        );

        $container['scheduler.job.pool'] = function ($container) {
            return new JobPool($container);
        };

        $container['autoload.aliases']['Scheduler'] = 'Codeages\Biz\Framework\Scheduler';
    }
}