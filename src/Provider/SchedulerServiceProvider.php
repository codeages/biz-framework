<?php

namespace Codeages\Biz\Framework\Provider;

use Codeages\Biz\Framework\Scheduler\Scheduler;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class SchedulerServiceProvider implements ServiceProviderInterface
{
    public function register(Container $biz)
    {
        $biz['autoload.aliases']['Scheduler'] = 'Codeages\Biz\Framework\Scheduler';

        $biz['scheduler.options'] = array(
            'max_num' => 10,
            'timeout' => 120,
        );

        $biz['console.commands'][] = function () use ($biz) {
            return new \Codeages\Biz\Framework\Scheduler\Command\TableCommand($biz);
        };
    }
}
