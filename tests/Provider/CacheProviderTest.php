<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Codeages\Biz\Framework\Context\Biz;
use Codeages\Biz\Framework\Provider\CacheServiceProvider;

class CacheProviderTest extends TestCase
{
    public function testRegister()
    {
        $biz = new Biz();
        $provider = new CacheServiceProvider();
        $biz->register($provider, array(
            'cache.options' => array(
                'driver' => 'redis',
                'host' => '127.0.0.1:6379',
                'timeout' => 1,
                'reserved' => 1,
                'retry_interval' => 100,
            )
        ));
    }
}
