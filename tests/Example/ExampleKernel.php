<?php

namespace Codeages\Biz\Framework\Tests\Example;

use Codeages\Biz\Framework\Context\Kernel;

class ExampleKernel extends Kernel
{
    public function __construct()
    {
        parent::__construct(include __DIR__.'/parameters.php');
    }

    public function getNamespace()
    {
        return __NAMESPACE__;
    }

    public function registerProviders()
    {
        return array(
            new ExampleServiceProvider(),
        );
    }

    public function recreateDatabase()
    {
        $this['db']->exec('DROP TABLE IF EXISTS example;');
        $this['db']->exec("
            CREATE TABLE `example` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `name` varchar(32) NOT NULL,
              `counter1` int(10) unsigned NOT NULL DEFAULT 0,
              `counter2` int(10) unsigned NOT NULL DEFAULT 0,
              `ids1` varchar(32) NOT NULL DEFAULT '',
              `ids2` varchar(32) NOT NULL DEFAULT '',
              `created` int(10) unsigned NOT NULL DEFAULT 0,
              `updated` int(10) unsigned NOT NULL DEFAULT 0,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
    }

    public function emptyDatabase()
    {
        $this['db']->exec('TRUNCATE example;');
    }
}
