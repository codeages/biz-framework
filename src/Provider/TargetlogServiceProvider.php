<?php

namespace Codeages\Biz\Framework\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Codeages\Biz\Framework\Context\MigrationProviderInterface;
use Codeages\Biz\Framework\Context\Kernel;
use Codeages\Biz\Targetlog\Dao\Impl\TargetlogDaoImpl;
use Codeages\Biz\Targetlog\Service\Impl\TargetlogServiceImpl;

class TargetlogServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container['migration.directories'][] = dirname(dirname(__DIR__)) . '/migrations/targetlog';
        $container['autoload.aliases']['Targetlog'] = 'Codeages\Biz\Framework\Targetlog';
    }
}
