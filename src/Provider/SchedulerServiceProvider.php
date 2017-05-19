<?php

namespace Codeages\Biz\Framework\Provider;

use Codeages\Biz\Framework\Scheduler\Checker\ExecutingChecker;
use Codeages\Biz\Framework\Scheduler\Pool\JobPool;
use Codeages\Biz\Framework\Scheduler\Checker\CheckerChain;
use Codeages\Biz\Framework\Scheduler\Checker\MisfireChecker;
use Codeages\Biz\Framework\Scheduler\Scheduler;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class SchedulerServiceProvider implements ServiceProviderInterface
{
    public function register(Container $biz)
    {
        $biz['migration.directories'][] = dirname(dirname(__DIR__)).'/migrations/scheduler';
        $biz['autoload.aliases']['Scheduler'] = 'Codeages\Biz\Framework\Scheduler';

        $biz['scheduler.job.pool.options'] = array(
            'max_num'  => 10,
            'timeout' => 120,
        );

        $biz['scheduler.job.pool'] = function ($biz) {
            return new JobPool($biz);
        };

        $biz['scheduler.job.checker_chain'] = function ($biz) {
            return new CheckerChain($biz);
        };

        $biz['scheduler.job.checkers'] = array(
            new MisfireChecker($biz),
            new ExecutingChecker($biz)
        );
    }
}