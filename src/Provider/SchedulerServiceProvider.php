<?php

namespace Codeages\Biz\Framework\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class SchedulerServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        $container['migration.directories'][] = dirname(dirname(__DIR__)).'/migrations/scheduler';
    }
}