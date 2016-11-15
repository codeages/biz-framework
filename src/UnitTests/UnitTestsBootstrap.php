<?php

namespace Codeages\Biz\Framework\UnitTests;

use Phpmig\Api\PhpmigApplication;
use Symfony\Component\Console\Output\NullOutput;
use Codeages\Biz\Framework\Dao\MigrationBootstrap;

class UnitTestsBootstrap
{
    protected $biz;

    public function __construct($biz)
    {
        $this->biz = $biz;
    }

    public function boot()
    {
        if (isset($this->biz['db.options'])) {
            $options = $this->biz['db.options'];
            $options['wrapperClass'] = 'Codeages\Biz\Framework\Dao\TestCaseConnection';
            $this->biz['db.options'] = $options;
        }

        BaseTestCase::setBiz($this->biz);
        BaseTestCase::emptyDatabase(true);

        $migration = new MigrationBootstrap($this->biz['db'], $this->biz['migration.directories']);
        $container = $migration->boot();

        $adapter = $container['phpmig.adapter'];
        if (!$adapter->hasSchema()) {
            $adapter->createSchema();
        }

        $app = new PhpmigApplication($container, new NullOutput());

        $app->up();
    }
}
