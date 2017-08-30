<?php

namespace Codeages\Biz\Framework\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Codeages\Biz\Framework\Targetlog\Command\TableCommand;

class TargetlogServiceProvider implements ServiceProviderInterface
{
    public function register(Container $biz)
    {
        $biz['migration.directories'][] = dirname(dirname(__DIR__)).'/migrations/targetlog';
        $biz['autoload.aliases']['Targetlog'] = 'Codeages\Biz\Framework\Targetlog';

        $biz['console.commands'][] = function () use ($biz) {
            return new TableCommand($biz);
        };
    }
}
