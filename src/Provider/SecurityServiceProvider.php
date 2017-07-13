<?php

namespace Codeages\Biz\Framework\Provider;

use Codeages\Biz\Framework\Security\Generator\DefaultSessionIdGenerator;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class SecurityServiceProvider implements ServiceProviderInterface
{
    public function register(Container $biz)
    {
        $biz['migration.directories'][] = dirname(dirname(__DIR__)).'/migrations/security';
        $biz['autoload.aliases']['Security'] = 'Codeages\Biz\Framework\Security';

        $biz['session.manager.sess_id_generator.default'] = function () {
            return new DefaultSessionIdGenerator();
        };

        $biz['session.manager.timeout.default'] = 86400;
    }
}
