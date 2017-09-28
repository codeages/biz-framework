<?php

namespace Codeages\Biz\Framework\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class XapiServiceProvider implements ServiceProviderInterface
{
    public function register(Container $biz)
    {
        $biz['autoload.aliases']['Xapi'] = 'Codeages\\Biz\\Framework\\Xapi';

        $biz['xapi.options'] = array(
            'version' => '1.0.0'
        );

        $biz['console.commands'][] = function () use ($biz) {
            return new \Codeages\Biz\Framework\Xapi\Command\TableCommand($biz);
        };
    }
}
